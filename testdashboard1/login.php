<?php
header('Content-Type: application/json');
session_start();

// Configuración base de datos
$host = 'localhost';
$db   = 'refugio_animales';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Conexión PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión']);
    exit;
}

// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$usuario = $data['usuario'] ?? '';
$password = $data['password'] ?? '';

if(!$usuario || !$password){
    echo json_encode(['status'=>'error','message'=>'Faltan datos']);
    exit;
}

// Consulta segura
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND password = SHA2(:password, 256)");
$stmt->execute(['usuario'=>$usuario, 'password'=>$password]);
$user = $stmt->fetch();

if($user){
    echo json_encode(['status'=>'success','usuario'=>$user['usuario'],'rol'=>$user['rol']]);
    $_SESSION["Usuario"]= $user['usuario'];
    $_SESSION["Rol"]= $user['rol'];
}else{
    echo json_encode(['status'=>'error','message'=>'Usuario o contraseña incorrectos']);
}
?>
