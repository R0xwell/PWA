<?php
header('Content-Type: application/json');

$host = "localhost";
$db = "refugio_animales";
$user = "root";
$pass = "";

// Conexión
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM notificaciones ORDER BY fecha DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

elseif ($method === 'POST') {
    // ✅ Agregar notificación
    $data = json_decode(file_get_contents("php://input"), true);
    if(!isset($data['tipo'], $data['accion'], $data['mensaje'])){
        http_response_code(400);
        echo json_encode(["error"=>"Faltan datos"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO notificaciones (tipo, accion, mensaje) VALUES (?,?,?)");
    $stmt->execute([$data['tipo'], $data['accion'], $data['mensaje']]);
    $idNotificacion = $pdo->lastInsertId();

    // 🔥 Después de insertar, enviar notificación FCM
    $resultadoEnvio = enviarNotificacionFirebase($pdo, $data['tipo'], $data['accion'], $data['mensaje']);

    echo json_encode([
        "success" => true,
        "idNotificacion" => $idNotificacion,
        "envio_notificacion" => $resultadoEnvio
    ]);
    exit;
}

elseif ($method === 'DELETE') {
    $pdo->exec("TRUNCATE TABLE notificaciones");
    echo json_encode(["success"=>true]);
    exit;
}

else {
    http_response_code(405);
    echo json_encode(["error"=>"Método no permitido"]);
    exit;
}

// =====================================================
// 🚀 Función para enviar notificación Firebase
// =====================================================
function enviarNotificacionFirebase($pdo, $tipo, $accion, $mensaje) {
    $serviceAccountPath = __DIR__ . "/firebase-key.json";
    if (!file_exists($serviceAccountPath)) {
        return ["error" => "No se encontró firebase-key.json"];
    }

    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    if (!$serviceAccount || !isset($serviceAccount['project_id'], $serviceAccount['private_key'])) {
        return ["error" => "firebase-key.json inválido o incompleto"];
    }

    $tokens = $pdo->query("SELECT token FROM dispositivos WHERE token IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tokens)) return ["error" => "No hay tokens registrados"];

    $accessToken = obtenerAccessToken($serviceAccount);
    if (!$accessToken) return ["error" => "No se pudo obtener access_token"];

    $projectId = $serviceAccount['project_id'];
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    $title = "$tipo - $accion";
    $body = $mensaje;
    $link = "https://tusitio.com/testdashboard1/";

    $resultados = [];
    $tokensInvalidos = 0;

    foreach ($tokens as $token) {
        $payload = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ],
                "webpush" => [
                    "notification" => [
                        "icon" => "https://tusitio.com/testdashboard1/resources/icon-512x512.png"
                    ],
                    "fcm_options" => [
                        "link" => $link
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonResp = json_decode($response, true);
        $ok = isset($jsonResp['name']);

        if (!$ok) {
            $msg = strtolower(json_encode($jsonResp));
            if (strpos($msg, 'unregistered') !== false || strpos($msg, 'not found') !== false) {
                $pdo->prepare("DELETE FROM dispositivos WHERE token = ?")->execute([$token]);
                $tokensInvalidos++;
            }
        }

        $resultados[] = [
            "token" => $token,
            "ok" => $ok,
            "http" => $httpCode,
            "response" => $jsonResp
        ];
    }

    return [
        "enviados" => count($tokens),
        "tokens_invalidos" => $tokensInvalidos,
        "detalles" => $resultados
    ];
}

// =====================================================
// 🔑 Generar access_token desde service account
// =====================================================
function obtenerAccessToken($serviceAccount) {
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $now = time();
    $payload = [
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];

    $base64UrlEncode = fn($data) => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    $jwtHeader = $base64UrlEncode($header);
    $jwtPayload = $base64UrlEncode($payload);
    $toSign = "$jwtHeader.$jwtPayload";

    openssl_sign($toSign, $signature, $serviceAccount['private_key'], 'SHA256');
    $jwtSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    $jwt = "$toSign.$jwtSignature";

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    return $data['access_token'] ?? null;
}
?>
