<?php
// testdashboard1/php/mascotas.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// CONFIGURACIÓN BD
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "refugio_animales";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// ---------------------- 🔥 FUNCION: Guardar notificación interna ----------------------
function agregarNotificacion($conn, $tipo, $accion, $mensaje) {
    $stmt = $conn->prepare("INSERT INTO notificaciones (tipo, accion, mensaje) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tipo, $accion, $mensaje);
    $stmt->execute();
}

// ---------------------- 🔥 FUNCION: Obtener Access Token OAuth2 para HTTP v1 ----------------------
function obtenerAccessToken() {
    $rutaKey = __DIR__ . "/firebase-key.json";

    if (!file_exists($rutaKey)) {
        return null;
    }

    $json = json_decode(file_get_contents($rutaKey), true);

    $tokenUrl = "https://oauth2.googleapis.com/token";

    $jwtHeader = [
        "alg" => "RS256",
        "typ" => "JWT"
    ];

    $iat = time();
    $exp = $iat + 3600;

    $jwtClaimSet = [
        "iss" => $json["client_email"],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => $tokenUrl,
        "iat" => $iat,
        "exp" => $exp
    ];

    $header = rtrim(strtr(base64_encode(json_encode($jwtHeader)), '+/', '-_'), '=');
    $payload = rtrim(strtr(base64_encode(json_encode($jwtClaimSet)), '+/', '-_'), '=');

    openssl_sign("$header.$payload", $signature, $json["private_key"], "sha256");

    $jwt = "$header.$payload." . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $postData = [
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion"  => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result["access_token"] ?? null;
}

// ---------------------- 🔥 FUNCION: Enviar Push HTTP v1 ----------------------
function enviarPushV1($conn, $titulo, $cuerpo, $link) {

    $projectId = "refugioanimal-b6096";
    $accessToken = obtenerAccessToken();

    if (!$accessToken) return;

    // Obtener tokens de dispositivos
    $tokens = [];
    $res = $conn->query("SELECT token FROM dispositivos");
    while ($row = $res->fetch_assoc()) {
        $tokens[] = $row["token"];
    }

    if (empty($tokens)) return;

    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

    foreach ($tokens as $token) {

        $body = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $titulo,
                    "body"  => $cuerpo
                ],
                "webpush" => [
                    "fcm_options" => [
                        "link" => $link
                    ],
                    "notification" => [
                        "icon" => "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/resources/icon-512x512.png"
                    ]
                ]
            ]
        ];

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        curl_exec($ch);
        curl_close($ch);
    }
}

// ---------------------- 🔥 RUTAS CRUD ----------------------
switch ($method) {

    case 'GET':
        $result = $conn->query("SELECT * FROM mascotas");
        $mascotas = [];
        while ($row = $result->fetch_assoc()) {
            $mascotas[] = $row;
        }
        echo json_encode($mascotas);
        break;

    case 'POST':
        $nombre = $input['nombre'] ?? '';
        $sexo = $input['sexo'] ?? '';
        $raza = $input['raza'] ?? '';
        $peso = $input['peso'] ?? 0;
        $condiciones = $input['condiciones'] ?? '';

        $stmt = $conn->prepare("INSERT INTO mascotas (nombre, sexo, raza, peso, condiciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $nombre, $sexo, $raza, $peso, $condiciones);
        $stmt->execute();

        // Crear notificación
        $mensaje = "Se agregó la mascota: $nombre";
        agregarNotificacion($conn, "Mascota", "Agregado", $mensaje);

        // Enviar PUSH
        enviarPushV1(
            $conn,
            "Nueva mascota registrada",
            $mensaje,
            "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/mascotas.html"
        );

        echo json_encode(["idMascota" => $conn->insert_id]);
        break;

    case 'PUT':
        $id = $input['idMascota'] ?? 0;
        $nombre = $input['nombre'] ?? '';
        $sexo = $input['sexo'] ?? '';
        $raza = $input['raza'] ?? '';
        $peso = $input['peso'] ?? 0;
        $condiciones = $input['condiciones'] ?? '';

        $stmt = $conn->prepare("UPDATE mascotas SET nombre=?, sexo=?, raza=?, peso=?, condiciones=? WHERE idMascota=?");
        $stmt->bind_param("sssdsi", $nombre, $sexo, $raza, $peso, $condiciones, $id);
        $stmt->execute();

        $mensaje = "Se actualizó la mascota: $nombre (ID: $id)";
        agregarNotificacion($conn, "Mascota", "Editado", $mensaje);

        enviarPushV1(
            $conn,
            "Mascota actualizada",
            $mensaje,
            "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/mascotas.html"
        );

        echo json_encode(["status" => "ok"]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? 0;

        $result = $conn->query("SELECT nombre FROM mascotas WHERE idMascota=$id");
        $nombre = ($row = $result->fetch_assoc()) ? $row['nombre'] : "";

        $stmt = $conn->prepare("DELETE FROM mascotas WHERE idMascota=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($nombre) {
            $mensaje = "Se eliminó la mascota: $nombre (ID: $id)";
            agregarNotificacion($conn, "Mascota", "Eliminado", $mensaje);

            enviarPushV1(
                $conn,
                "Mascota eliminada",
                $mensaje,
                "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/mascotas.html"
            );
        }

        echo json_encode(["status" => "ok"]);
        break;

    default:
        echo json_encode(["error" => "Método no soportado"]);
        break;
}

$conn->close();
?>
