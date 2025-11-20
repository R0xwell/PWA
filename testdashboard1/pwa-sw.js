// ======================================================================
// 🔥 FIREBASE CLOUD MESSAGING (FCM)
// ======================================================================
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging-compat.js");

// MISMA CONFIGURACIÓN QUE YA USAS
firebase.initializeApp({
  apiKey: "AIzaSyDYdFd-39KDzADVcfurLdT9_W2cAIChFRA",
  authDomain: "refugioanimal-b6096.firebaseapp.com",
  projectId: "refugioanimal-b6096",
  storageBucket: "refugioanimal-b6096.firebasestorage.app",
  messagingSenderId: "122878637057",
  appId: "1:122878637057:web:951a15ed750ce8fca27c00"
});

const messaging = firebase.messaging();

// 📩 Manejar mensajes en background (app cerrada)
messaging.onBackgroundMessage((payload) => {
  console.log("📨 Notificación background (FCM):", payload);

  const title = payload.notification?.title || "Notificación";
  const options = {
    body: payload.notification?.body || "",
    icon: "/testdashboard1/resources/icon-512x512.png",
    data: {
      link: payload.fcmOptions?.link || "/"
    }
  };

  self.registration.showNotification(title, options);
});


// ======================================================================
// 📡 FALLBACK PARA PUSH (por si el servidor manda notificación sin FCM)
// ======================================================================
self.addEventListener("push", (event) => {
  console.log("📩 Push recibido (web-push/fallback):", event);

  if (!event.data) return;

  let data = {};
  try {
    data = event.data.json();
  } catch (e) {
    console.warn("⚠ No se pudo parsear JSON:", e);
  }

  const title = data.title || data.notification?.title || "Notificación";
  const options = {
    body: data.body || data.notification?.body || "",
    icon: data.icon || "/testdashboard1/resources/icon-512x512.png",
    data: {
      link: data.url || data.fcmOptions?.link || "/"
    }
  };

  event.waitUntil(self.registration.showNotification(title, options));
});


// ======================================================================
// 🎯 CLICK EN NOTIFICACIÓN
// ======================================================================
self.addEventListener("notificationclick", function (event) {
  event.notification.close();

  const url = event.notification.data.link || "/testdashboard1/home.html";

  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
      for (let client of windowClients) {
        if (client.url.includes(url) && "focus" in client) {
          return client.focus();
        }
      }
      return clients.openWindow(url);
    })
  );
});


// ======================================================================
// 📦 PWA SERVICE WORKER ORIGINAL (NO MODIFICADO)
// ======================================================================

// Ruta base del proyecto
const BASE_PATH = '/testdashboard1/';

function url(file) {
  return `${BASE_PATH}${file || ''}`;
}

const PRECACHENAME = "dfhash-precache-v3";
const SYNCEVENTNAME = "dfhash-sync-notifications";
const PERIODICSYNCEVENTNAME = "dfhash-periodic-sync-notifications";
const OFFLINEURL = url("offline.html");

// Archivos offline
const OFFLINE_ASSETS = [
  url('index.html'),
  url('home.html'),
  url('views/mascotas.html'),
  url('views/padrinos.html'),
  url('views/apoyos.html'),
  url('views/notifications.html'),
  url('css/index.css'),
  url('css/home.css'),
  url('js/app.js'),
  url('resources/veterinaria_bigotes.png'),
  url('resources/icono.png'),
  url('resources/icono-maskable.png'),
  OFFLINEURL
];

// Instalación
self.addEventListener("install", event => {
  console.info("💾 SW: Instalando y precacheando recursos...");

  event.waitUntil(
    caches.open(PRECACHENAME)
      .then(cache => cache.addAll(OFFLINE_ASSETS))
      .catch(err => console.error("❌ Error al precachear:", err))
  );
});

// Activación
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

// Fetch
self.addEventListener("fetch", event => {
  if (!event.request.url.startsWith(self.location.origin)) return;

  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          const responseClone = response.clone();
          caches.open(PRECACHENAME).then(cache => cache.put(event.request, responseClone));
          return response;
        })
        .catch(async () => {
          const cache = await caches.open(PRECACHENAME);
          const path = new URL(event.request.url).pathname;

          if (path.includes("/views/")) {
            const viewFile = path.replace(BASE_PATH, '');
            const cachedView = await cache.match(url(viewFile));
            if (cachedView) return cachedView;
          }

          return (await cache.match(url('home.html'))) || (await cache.match(OFFLINEURL));
        })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(resp => resp || fetch(event.request))
      .catch(() => caches.match(OFFLINEURL))
  );
});

// Sync event
function syncNotifications() {}
function periodicSyncNotifications() {}

self.addEventListener("sync", event => {
  if (event.tag === SYNCEVENTNAME)
    event.waitUntil(syncNotifications(registration));
});

self.addEventListener("periodicsync", event => {
  if (event.tag === PERIODICSYNCEVENTNAME)
    event.waitUntil(periodicSyncNotifications(registration));
});
// ======================================================================
// 🌐 BACKGROUND SYNC / CRUD PARA APOYOS
// ======================================================================

const APYO_PENDING_STORE = "apoyos-pending-store";

// Abrir IndexedDB
function idbOpenDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open("pwa-refugio", 1);
    request.onupgradeneeded = event => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(APYO_PENDING_STORE)) {
        db.createObjectStore(APYO_PENDING_STORE, { keyPath: "timestamp" });
      }
    };
    request.onsuccess = event => resolve(event.target.result);
    request.onerror = event => reject(event.target.error);
  });
}

// Guardar operación pendiente
async function savePendingApoyo(data) {
  const db = await idbOpenDB();
  const tx = db.transaction(APYO_PENDING_STORE, "readwrite");
  tx.objectStore(APYO_PENDING_STORE).add({ ...data, timestamp: Date.now() });
  await tx.done;
}

// Sincronizar operaciones pendientes
async function syncPendingApoyos() {
  const db = await idbOpenDB();
  const tx = db.transaction(APYO_PENDING_STORE, "readwrite");
  const store = tx.objectStore(APYO_PENDING_STORE);
  const allOps = await store.getAll();

  for (let item of allOps) {
    try {
      const res = await fetch(item.url, {
        method: item.method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(item.body)
      });
      if (res.ok) {
        store.delete(item.timestamp);
        self.registration.showNotification("Apoyo sincronizado", {
          body: `El apoyo con ID ${item.body.idApoyo || ''} se sincronizó correctamente.`,
          icon: "/testdashboard1/resources/icon-512x512.png"
        });
      }
    } catch (e) {
      console.warn("⏳ No se pudo sincronizar apoyo:", item.body.idApoyo);
    }
  }
  await tx.done;
}

// Interceptar fetch para apoyos
self.addEventListener("fetch", event => {
  const url = new URL(event.request.url);
  if (!url.pathname.includes("/apoyos.php")) return;

  if (event.request.method === "POST") {
    event.respondWith((async () => {
      try {
        const clone = event.request.clone();
        const body = await clone.json();
        const method = body._method || "POST";

        // Enviar al servidor si hay conexión
        const resp = await fetch(event.request);
        return resp;
      } catch (err) {
        // Offline: guardar operación
        const clone = event.request.clone();
        const body = await clone.json();
        const method = body._method || "POST";
        await savePendingApoyo({ url: event.request.url, method, body });
        return new Response(JSON.stringify({ success: false, offline: true }), {
          headers: { "Content-Type": "application/json" }
        });
      }
    })());
  }
});

// Background sync
self.addEventListener("sync", event => {
  if (event.tag === "sync-apoyos") {
    event.waitUntil(syncPendingApoyos());
  }
});
