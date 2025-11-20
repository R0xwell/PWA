<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

/* ============================================================
   🔥 CONFIG BD
============================================================ */
$host = "localhost";
$dbname = "refugio_animales";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

/* ============================================================
   🔥 GUARDAR NOTIFICACIÓN INTERNA
============================================================ */
function agregarNotificacion($conn, $tipo, $accion, $mensaje) {
    $stmt = $conn->prepare("INSERT INTO notificaciones (tipo, accion, mensaje) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tipo, $accion, $mensaje);
    $stmt->execute();
}

/* ============================================================
   🔥 OBTENER ACCESS TOKEN OAuth2 PARA HTTP v1
============================================================ */
function obtenerAccessToken() {

    $rutaKey = __DIR__ . "/firebase-key.json";
    if (!file_exists($rutaKey)) return null;

    $json = json_decode(file_get_contents($rutaKey), true);

    $tokenUrl = "https://oauth2.googleapis.com/token";

    $jwtHeader = ["alg" => "RS256", "typ" => "JWT"];

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

/* ============================================================
   🔥 ENVIAR PUSH A TODOS LOS TOKENS REGISTRADOS
============================================================ */
function enviarPushV1($conn, $titulo, $cuerpo, $link) {

    $projectId = "refugioanimal-b6096";
    $accessToken = obtenerAccessToken();

    if (!$accessToken) return;

    // Obtener tokens
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

/* ============================================================
   CRUD PADRINOS
============================================================ */

switch ($method) {

    case 'GET':
        $res = $conn->query("SELECT * FROM padrinos");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;

        echo json_encode($data);
        break;


    case 'POST':
        $nombre = $input['nombrePadrino'];
        $sexo = $input['sexo'];
        $telefono = $input['telefono'];
        $correo = $input['correoElectronico'];

        $stmt = $conn->prepare("INSERT INTO padrinos (nombrePadrino, sexo, telefono, correoElectronico) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $sexo, $telefono, $correo);
        $stmt->execute();

        $id = $conn->insert_id;

        // Notificación interna
        $mensaje = "Se agregó un nuevo padrino: $nombre (ID $id)";
        agregarNotificacion($conn, "Padrino", "Agregado", $mensaje);

        // Push
        enviarPushV1(
            $conn,
            "Nuevo padrino agregado",
            $mensaje,
            "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/padrinos.html"
        );

        echo json_encode(["idPadrino" => $id]);
        break;


    case 'PUT':
        $id = $input['idPadrino'];
        $nombre = $input['nombrePadrino'];
        $sexo = $input['sexo'];
        $telefono = $input['telefono'];
        $correo = $input['correoElectronico'];

        $stmt = $conn->prepare("UPDATE padrinos SET nombrePadrino=?, sexo=?, telefono=?, correoElectronico=? WHERE idPadrino=?");
        $stmt->bind_param("ssssi", $nombre, $sexo, $telefono, $correo, $id);
        $stmt->execute();

        $mensaje = "Se actualizó el padrino: $nombre (ID: $id)";
        agregarNotificacion($conn, "Padrino", "Editado", $mensaje);

        enviarPushV1(
            $conn,
            "Padrino actualizado",
            $mensaje,
            "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/padrinos.html"
        );

        echo json_encode(["status" => "ok"]);
        break;


    case 'DELETE':
        $id = $_GET['id'];

        $res = $conn->query("SELECT nombrePadrino FROM padrinos WHERE idPadrino=$id");
        $nombre = ($row = $res->fetch_assoc()) ? $row['nombrePadrino'] : "";

        $conn->query("DELETE FROM padrinos WHERE idPadrino=$id");

        if ($nombre) {
            $mensaje = "Se eliminó el padrino: $nombre (ID: $id)";
            agregarNotificacion($conn, "Padrino", "Eliminado", $mensaje);

            enviarPushV1(
                $conn,
                "Padrino eliminado",
                $mensaje,
                "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/padrinos.html"
            );
        }

        echo json_encode(["status" => "ok"]);
        break;

    default:
        echo json_encode(["error" => "Método no permitido"]);
}

$conn->close();
?>
