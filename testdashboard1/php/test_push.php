<?php
// =======================================================
// 🚀 Enviar notificación de prueba usando FCM HTTP v1
// =======================================================

header('Content-Type: text/html; charset=utf-8');

$serviceAccountPath = __DIR__ . "/firebase-key.json";
if (!file_exists($serviceAccountPath)) {
  die("❌ No se encontró el archivo firebase-key.json");
}

$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
$projectId = $serviceAccount['project_id'];

// =======================================================
// 1️⃣ Generar token de acceso OAuth 2.0
// =======================================================
function getAccessToken($serviceAccount) {
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
  $signatureInput = "$jwtHeader.$jwtPayload";

  openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'SHA256');
  $jwtSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
  $jwt = "$signatureInput.$jwtSignature";

  $response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => 'Content-Type: application/x-www-form-urlencoded',
      'content' => http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
      ])
    ]
  ]));

  $data = json_decode($response, true);
  return $data['access_token'] ?? null;
}

$accessToken = getAccessToken($serviceAccount);
if (!$accessToken) {
  die("❌ No se pudo generar el token de acceso.");
}

// =======================================================
// 2️⃣ Leer token de dispositivo (manual o desde BD)
// =======================================================

$token = $_POST['token'] ?? $_GET['token'] ?? null;
if (!$token) {
  echo "<h3>🧪 Prueba tu token aquí:</h3>
  <form method='GET'>
    <input type='text' name='token' style='width: 600px' placeholder='Pega aquí el token del dispositivo' required>
    <button type='submit'>Enviar notificación</button>
  </form>";
  exit;
}

// =======================================================
// 3️⃣ Armar payload de mensaje
// =======================================================
$payload = [
  "message" => [
    "token" => $token,
    "notification" => [
      "title" => "🚀 Prueba desde test_push.php",
      "body" => "Esto es un mensaje directo desde el servidor",
    ],
    "webpush" => [
      "notification" => [
        "icon" => "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/resources/icon-512x512.png",
        "badge" => "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/resources/badge.png"
      ],
      "fcm_options" => [
        "link" => "https://marc-preimperial-charmain.ngrok-free.dev/testdashboard1/mascotas.html"
      ]
    ]
  ]
];

// =======================================================
// 4️⃣ Enviar solicitud al endpoint de FCM
// =======================================================
$url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $accessToken",
  "Content-Type: application/json"
]); 
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

// =======================================================
// 5️⃣ Mostrar resultado
// =======================================================
echo "<h3>📤 Enviando notificación...</h3>";
echo "<b>Token:</b> <code>$token</code><br><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
