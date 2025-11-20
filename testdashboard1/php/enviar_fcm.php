<?php

function obtenerAccessToken() {
    $rutaKey = __DIR__ . "/firebase-key.json";

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


function enviarPushV1($token, $titulo, $cuerpo, $link) {

    $projectId = "refugioanimal-b6096";
    $accessToken = obtenerAccessToken();

    if (!$accessToken) return "ERROR: No se pudo obtener access token";

    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

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

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}
?>
