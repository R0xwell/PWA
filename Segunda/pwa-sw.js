// ===============================
// 📦 PWA Service Worker
// ===============================

// Ruta base del proyecto
const BASE_PATH = '/testdashboard/';

function url(file) {
  return `${BASE_PATH}${file || ''}`;
}

const PRECACHENAME = "dfhash-precache-v3";
const SYNCEVENTNAME = "dfhash-sync-notifications";
const PERIODICSYNCEVENTNAME = "dfhash-periodic-sync-notifications";
const OFFLINEURL = url("offline.html");

// ===============================
// 🗂️ Archivos a cachear
// ===============================
const OFFLINE_ASSETS = [
  // Páginas principales
  url('index.html'),
  url('home.html'),

  // ✅ Vistas internas
  url('views/mascotas.html'),
  url('views/padrinos.html'),
  url('views/apoyos.html'),
  url('views/notifications.html'),

  // Estilos y scripts
  url('css/index.css'),
  url('js/app.js'),

  // Recursos e íconos
  url('resources/veterinaria_bigotes.png'),
  url('resources/icono.png'),
  url('resources/icono-maskable.png'),

  // Página offline
  OFFLINEURL
];

// ===============================
// 🧩 Instalación del SW
// ===============================
self.addEventListener("install", event => {
  console.info("💾 SW: Instalando y precacheando recursos...");

  event.waitUntil(
    caches.open(PRECACHENAME)
      .then(cache => {
        console.info("✅ SW: Archivos guardados en caché.");
        return cache.addAll(OFFLINE_ASSETS);
      })
      .catch(err => console.error("❌ Error al precachear:", err))
  );
});

// ===============================
// ⚙️ Activación
// ===============================
self.addEventListener("activate", event => {
  console.info("⚡ SW: Activado. Limpiando cachés antiguas...");

  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== PRECACHENAME)
            .map(key => caches.delete(key))
      );
    })
  );

  return self.clients.claim();
});

// ===============================
// 🌐 Manejo de peticiones (fetch)
// ===============================
self.addEventListener("fetch", event => {
  // Solo manejar peticiones HTTP o de navegación
  if (!event.request.url.startsWith(self.location.origin)) return;

  // Si es una navegación (HTML)
  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Almacenar nueva versión en caché (actualización automática)
          const responseClone = response.clone();
          caches.open(PRECACHENAME).then(cache => cache.put(event.request, responseClone));
          return response;
        })
        .catch(async () => {
          // Si no hay conexión, intentar servir desde caché
          const cache = await caches.open(PRECACHENAME);

          // Detectar si es una vista conocida
          const requestPath = new URL(event.request.url).pathname;

          if (requestPath.includes("/views/")) {
            const viewFile = requestPath.replace(BASE_PATH, '');
            const cachedView = await cache.match(url(viewFile));
            if (cachedView) return cachedView;
          }

          // Si no es vista, devolver home u offline
          return (await cache.match(url('home.html'))) || (await cache.match(OFFLINEURL));
        })
    );
    return;
  }

  // Si es otro tipo de recurso (CSS, JS, imagen, etc.)
  event.respondWith(
    caches.match(event.request)
      .then(resp => resp || fetch(event.request))
      .catch(() => caches.match(OFFLINEURL))
  );
});

// ===============================
// 🔔 Notificaciones Push y Sync
// ===============================
function syncNotifications(reg) {}
function periodicSyncNotifications(reg) {}

function sendOneNotification(reg, title, body) {
  if (Notification.permission !== "granted") return false;

  reg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: "GENERA_TU_APPLICATION_SERVER_KEY"
  }).then(pushSubscription => {
    const data = new FormData();
    data.append("sub", JSON.stringify(pushSubscription));
    data.append("title", title);
    data.append("body", body);

    fetch(url("web-push-push-server.php"), { method: "POST", body: data })
      .then(res => res.text())
      .then(txt => console.log("📨 Notificación enviada:", txt))
      .catch(err => console.error("❌ Error en notificación:", err));
  }).catch(err => console.error("❌ Error al suscribir push:", err));
}

// ===============================
// 🔄 Eventos de sincronización
// ===============================
self.addEventListener("sync", event => {
  if (event.tag === SYNCEVENTNAME)
    event.waitUntil(syncNotifications(registration));
});

self.addEventListener("periodicsync", event => {
  if (event.tag === PERIODICSYNCEVENTNAME)
    event.waitUntil(periodicSyncNotifications(registration));
});

// ===============================
// 📢 Recepción de notificaciones push
// ===============================
self.addEventListener("push", event => {
  const data = event.data.json();
  self.registration.showNotification(data.title, {
    body: data.body,
    icon: data.icon || url("resources/icono.png"),
    image: data.image
  });
});
