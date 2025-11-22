🐾 Refugio Animal – Módulo de Apoyos (PWA + Push Notifications)

Sistema web para administrar apoyos económicos en un refugio animal.
Incluye CRUD, soporte offline, PWA, Service Worker, sincronización local y notificaciones push mediante Firebase Cloud Messaging.

🚀 Tecnologías

Frontend: AngularJS 1.8

Backend: PHP 7+ (API REST)

Base de datos: MySQL

Push Notifications: Firebase Cloud Messaging (FCM v1)

PWA: Manifest + Service Worker + Caché dinámico

Contenedores: Docker

Hosting compatible: Render, CPanel, etc.

📦 Funciones Principales

Agregar, editar y eliminar apoyos

Funciona offline y sincroniza cuando vuelve la conexión

Guardado local con localStorage

Push notifications al agregar/editar/eliminar apoyos

Token FCM registrado por dispositivo

Service Worker para modo offline e íconos

API REST completa con PHP

Compatible con móviles y computadoras

🔔 Push Notifications – Integración
Frontend (solicitud de token)
messaging.requestPermission()
.then(() => messaging.getToken())
.then(token => {
  fetch("php/guardar_token.php", {
    method: "POST",
    body: JSON.stringify({token}),
    headers: {"Content-Type": "application/json"}
  });
});

Service Worker (mostrar notificación)
messaging.setBackgroundMessageHandler(payload => {
  return self.registration.showNotification(payload.notification.title, {
    body: payload.notification.body,
    icon: "/resources/icon-512x512.png"
  });
});

🗄 Backend – Envío de notificaciones
$url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
$headers = [
  "Authorization: Bearer $accessToken",
  "Content-Type: application/json"
];


Los tokens se guardan en la tabla:

CREATE TABLE dispositivos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token TEXT NOT NULL
);

📁 Estructura del Proyecto
/resources
/service-worker
/php
 ├── apoyos.php
 ├── guardar_token.php
 └── firebase-key.json
/js
css
manifest.json
index.html
README.md

📦 Docker

Archivo Dockerfile preparado para:

PHP 8

Apache

Extensiones mysqli

Carpeta /var/www/html con app completa

🧪 Pruebas

✔ CRUD funcional
✔ Push notifications en móvil y escritorio
✔ Notificaciones funcionando con app cerrada
✔ Modo offline operativo
✔ Probado via túnel (Ngrok / Cloudflare Tunnel)

📎 Repositorio
https://github.com/R0xwell/PWA/tree/main/testdashboard1

📄 Licencia

Proyecto académico – uso libre con créditos.