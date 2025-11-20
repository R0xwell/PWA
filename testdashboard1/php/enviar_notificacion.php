<?php
// enviar_notificacion.php (mejorado)
// Envía la última notificación de la tabla `notificaciones` a todos los tokens guardados
// Requisitos: firebase-key.json en la misma carpeta, tabla `dispositivos(token)`, tabla `notificaciones`

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Config DB ---
$host = "localhost";
$db   = "refugio_animales";
$user = "root";
$pass = "";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
} catch (Exception $e) {
  die("❌ Error DB: " . $e->getMessage());
}

// --- Obtener tokens ---
$tokens = $pdo->query("SELECT token FROM dispositivos WHERE token IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
if (empty($tokens)) {
  die("⚠️ No hay tokens registrados en la base de datos");
}

// --- Obtener la notificación a enviar ---
// Primero intento recibir parámetros POST (por si llamas al script con datos),
// si no, tomo la última fila de la tabla notificaciones.
$title = $_POST['title'] ?? null;
$body  = $_POST['body']  ?? null;
$link  = $_POST['link']  ?? null;

if (!$title || !$body) {
  $stmt = $pdo->query("SELECT idNotificacion, tipo, accion, mensaje, fecha FROM notificaciones ORDER BY fecha DESC LIMIT 1");
  $notif = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$notif) die("❌ No hay notificaciones en la tabla para enviar.");
  $title = ($notif['tipo'] ? $notif['tipo'] . ' - ' : '') . ($notif['accion'] ?? 'Notificación');
  $body  = $notif['mensaje'] ?? '';
  $link  = $link ?? ''; // opcional
}

// --- Cargar credenciales de servicio ---
$serviceAccountPath = __DIR__ . "/firebase-key.json";
if (!file_exists($serviceAccountPath)) die("❌ No se encontró firebase-key.json en: $serviceAccountPath");
$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
if (!$serviceAccount) die("❌ firebase-key.json inválido");

// --- Generar access token OAuth2 (JWT) ---
function getAccessToken($serviceAccount) {
  $header = ['alg' => 'RS256','typ' => 'JWT'];
  $now = time();
  $payload = [
    'iss' => $serviceAccount['client_email'],
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600
  ];

  $base64UrlEncode = function($data) {
    return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
  };

  $jwtHeader = $base64UrlEncode($header);
  $jwtPayload = $base64UrlEncode($payload);
  $toSign = $jwtHeader . '.' . $jwtPayload;

  $privateKey = $serviceAccount['private_key'];
  $signature = '';
  if (!openssl_sign($toSign, $signature, $privateKey, 'SHA256')) {
    return null;
  }
  $jwtSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
  $jwt = $toSign . '.' . $jwtSignature;

  // POST a oauth2 token endpoint
  $ch = curl_init('https://oauth2.googleapis.com/token');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwt
  ]));
  $resp = curl_exec($ch);
  $err = curl_error($ch);
  curl_close($ch);
  if ($err) return null;

  $data = json_decode($resp, true);
  return $data['access_token'] ?? null;
}

$accessToken = getAccessToken($serviceAccount);
if (!$accessToken) die("❌ No se pudo obtener access_token. Revisa firebase-key.json y openssl.");

// --- Preparar endpoint ---
$projectId = $serviceAccount['project_id'] ?? null;
if (!$projectId) die("❌ project_id no encontrado en firebase-key.json");
$url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

// --- Función para enviar y procesar respuesta ---
function sendMessageToToken($url, $accessToken, $token, $title, $body, $link = '') {
  $message = [
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
          "link" => $link ?: "https://tusitio.com/testdashboard1/"
        ]
      ]
    ]
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$accessToken}",
    "Content-Type: application/json; charset=utf-8"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
  $resp = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);

  if ($err) {
    return ['ok'=>false, 'error'=>$err, 'http'=>$httpCode, 'raw'=>$resp];
  }
  $json = json_decode($resp, true);
  return ['ok'=>true, 'http'=>$httpCode, 'raw'=>$json];
}

// --- Enviar a cada token y manejar errores (eliminar tokens inválidos) ---
$results = [];
$removed = 0;
foreach ($tokens as $token) {
  $r = sendMessageToToken($url, $accessToken, $token, $title, $body, $link);
  // Normalmente una respuesta exitosa es un objeto con "name": "projects/.../messages/..."
  if ($r['ok'] && isset($r['raw']['name'])) {
    $results[] = ['token'=>$token,'status'=>'success','resp'=>$r['raw']];
  } else {
    // intentar detectar error en la respuesta
    $resp = $r['raw'];
    $remove = false;
    $reason = '';
    if (is_array($resp) && isset($resp['error'])) {
      $reason = $resp['error']['message'] ?? json_encode($resp['error']);
      // algunos mensajes de error comunes:
      // "Requested entity was not found." / "registration token not found" / "UNREGISTERED"
      $msg = strtolower($reason);
      if (strpos($msg, 'unregistered') !== false || strpos($msg, 'not found') !== false || strpos($msg, 'invalid') !== false) {
        $remove = true;
      }
    } else {
      $reason = is_string($resp) ? $resp : json_encode($resp);
    }

    $results[] = ['token'=>$token,'status'=>'error','reason'=>$reason];

    if ($remove) {
      // eliminar token de DB para evitar futuros intentos
      $stmt = $GLOBALS['pdo']->prepare("DELETE FROM dispositivos WHERE token = ?");
      $stmt->execute([$token]);
      $removed++;
    }
  }
}

// --- Mostrar resumen ---
echo "<h2>📤 Resumen de envío</h2>";
echo "<p>Título: <strong>".htmlspecialchars($title)."</strong></p>";
echo "<p>Body: ".htmlspecialchars($body)."</p>";
echo "<p>Enviados: ".count($tokens).", Eliminados inválidos: $removed</p>";
echo "<pre>";
print_r($results);
echo "</pre>";
?>
