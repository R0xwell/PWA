<?php
// CORS dinámico (permite cookies si el cliente las envía)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    // Devuelve el origin recibido (no usar '*' si vas a permitir credenciales)
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Responder OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Para preflight devolvemos 200 y salimos
    http_response_code(200);
    exit;
}

session_start();
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$db = "refugio_animales";
$user = "root";
$pass = "";

// Conexión a BD
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// Leer token (soporta form-urlencoded o JSON)
$token = null;
if (!empty($_POST['token'])) {
    $token = $_POST['token'];
} else {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (isset($json['token'])) $token = $json['token'];
}

if (!$token) {
    http_response_code(400);
    echo json_encode(["error" => "Falta el token"]);
    exit;
}

$token = trim($token);
// Validación simple de formato (ajusta longitud mínima)
if (strlen($token) < 20) {
    http_response_code(400);
    echo json_encode(["error" => "Token inválido"]);
    exit;
}

// Verificar usuario en sesión
$nombreUsuario = $_SESSION["Usuario"] ?? null;
if (!$nombreUsuario) {
    http_response_code(403);
    echo json_encode(["error" => "No hay usuario en sesión"]);
    exit;
}

// Buscar idUsuario
$stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE usuario = ?");
$stmt->execute([$nombreUsuario]);
$idUsuario = $stmt->fetchColumn();

if (!$idUsuario) {
    http_response_code(404);
    echo json_encode(["error" => "Usuario no encontrado en BD"]);
    exit;
}

// Verificar si el usuario ya tiene token
$stmt = $pdo->prepare("SELECT id, token FROM dispositivos WHERE idUsuario = ?");
$stmt->execute([$idUsuario]);
$dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dispositivo) {
    if ($dispositivo['token'] === $token) {
        echo json_encode([
            "success" => true,
            "mensaje" => "El usuario ya tiene este token registrado",
            "token" => $token,
            "idUsuario" => $idUsuario
        ]);
        exit;
    } else {
        $pdo->prepare("UPDATE dispositivos SET token = ?, fecha_registro = NOW() WHERE idUsuario = ?")
            ->execute([$token, $idUsuario]);

        echo json_encode([
            "success" => true,
            "mensaje" => "Token del usuario actualizado",
            "token" => $token,
            "idUsuario" => $idUsuario
        ]);
        exit;
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO dispositivos (idUsuario, token, fecha_registro) VALUES (?, ?, NOW())");
    $stmt->execute([$idUsuario, $token]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Token guardado correctamente",
        "token" => $token,
        "idUsuario" => $idUsuario
    ]);
    exit;
}
?>
