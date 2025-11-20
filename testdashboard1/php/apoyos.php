<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$conn = new mysqli("localhost", "root", "", "refugio_animales");
if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

function agregarNotificacion($conn, $tipo, $accion, $mensaje) {
    $stmt = $conn->prepare("INSERT INTO notificaciones (tipo, accion, mensaje) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tipo, $accion, $mensaje);
    $stmt->execute();
}

function obtenerAccessToken() {
    $rutaKey = __DIR__ . "/firebase-key.json";
    if (!file_exists($rutaKey)) return null;

    $json = json_decode(file_get_contents($rutaKey), true);
    $tokenUrl = "https://oauth2.googleapis.com/token";

    $header = base64_encode(json_encode(["alg"=>"RS256","typ"=>"JWT"]));
    $iat = time();
    $exp = $iat + 3600;
    $payload = base64_encode(json_encode([
        "iss"=>$json["client_email"],
        "scope"=>"https://www.googleapis.com/auth/firebase.messaging",
        "aud"=>$tokenUrl,
        "iat"=>$iat,
        "exp"=>$exp
    ]));

    openssl_sign("$header.$payload", $signature, $json["private_key"], "sha256");
    $jwt = "$header.$payload." . base64_encode($signature);

    $postData = ["grant_type"=>"urn:ietf:params:oauth:grant-type:jwt-bearer","assertion"=>$jwt];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response,true);
    return $result["access_token"] ?? null;
}

function enviarPushV1($conn, $titulo, $cuerpo, $link) {
    $projectId = "refugioanimal-b6096";
    $accessToken = obtenerAccessToken();
    if (!$accessToken) return;

    $tokens = [];
    $res = $conn->query("SELECT token FROM dispositivos");
    while($row = $res->fetch_assoc()) $tokens[] = $row["token"];
    if(empty($tokens)) return;

    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    foreach($tokens as $token) {
        $body = [
            "message"=>[
                "token"=>$token,
                "notification"=>["title"=>$titulo,"body"=>$cuerpo],
                "webpush"=>["fcm_options"=>["link"=>$link],"notification"=>["icon"=>"https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/resources/icon-512x512.png"]]
            ]
        ];
        $headers = ["Authorization: Bearer $accessToken","Content-Type: application/json"];
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
        curl_exec($ch);
        curl_close($ch);
    }
}

switch($method) {

    case 'GET':
        $res = $conn->query("SELECT * FROM apoyos ORDER BY idApoyo DESC");
        $data = [];
        while($row = $res->fetch_assoc()) {
            $data[] = [
                "idApoyo"   => intval($row["idApoyo"]),
                "idMascota" => intval($row["idMascota"]),
                "idPadrino" => intval($row["idPadrino"]),
                "monto"     => floatval($row["monto"]),
                "causa"     => $row["causa"]
            ];
        }
        echo json_encode($data);
        break;

    case 'POST':
        $idMascota = isset($input['idMascota']) && $input['idMascota'] !== "" ? intval($input['idMascota']) : 0;
        $idPadrino = isset($input['idPadrino']) && $input['idPadrino'] !== "" ? intval($input['idPadrino']) : 0;

        $monto = floatval($input['monto'] ?? 0);
        $causa = $input['causa'] ?? '';

        $stmt = $conn->prepare("INSERT INTO apoyos (idMascota, idPadrino, monto, causa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $idMascota, $idPadrino, $monto, $causa);
        $stmt->execute();
        $id = $conn->insert_id;

        $mensaje = "Nuevo apoyo (ID $id). Monto $monto — Causa: $causa.";
        agregarNotificacion($conn,"Apoyo","Agregado",$mensaje);
        enviarPushV1($conn,"Nuevo apoyo registrado",$mensaje,"https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/apoyos.html");

        echo json_encode(["idApoyo"=>$id]);
        break;

    case 'PUT':
        $id = $input['idApoyo'] ?? 0;

        $idMascota = isset($input['idMascota']) && $input['idMascota'] !== "" ? intval($input['idMascota']) : 0;
        $idPadrino = isset($input['idPadrino']) && $input['idPadrino'] !== "" ? intval($input['idPadrino']) : 0;

        $monto = floatval($input['monto'] ?? 0);
        $causa = $input['causa'] ?? '';

        $stmt = $conn->prepare("UPDATE apoyos SET idMascota=?, idPadrino=?, monto=?, causa=? WHERE idApoyo=?");
        $stmt->bind_param("iidsi",$idMascota,$idPadrino,$monto,$causa,$id);
        $stmt->execute();

        $mensaje = "Apoyo actualizado (ID $id).";
        agregarNotificacion($conn,"Apoyo","Editado",$mensaje);
        enviarPushV1($conn,"Apoyo actualizado",$mensaje,"https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/apoyos.html");

        echo json_encode(["status"=>"ok"]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        $res = $conn->query("SELECT monto, causa FROM apoyos WHERE idApoyo=$id");
        $row = $res->fetch_assoc();

        $stmt = $conn->prepare("DELETE FROM apoyos WHERE idApoyo=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();

        if($row) {
            $mensaje = "Apoyo eliminado (ID $id). Monto {$row['monto']} — Causa: {$row['causa']}.";
            agregarNotificacion($conn,"Apoyo","Eliminado",$mensaje);
            enviarPushV1($conn,"Apoyo eliminado",$mensaje,"https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/apoyos.html");
        }

        echo json_encode(["status"=>"ok"]);
        break;

    default:
        echo json_encode(["error"=>"Método no soportado"]);
        break;
}

$conn->close();
?>
