// ----------------------------
// IMPORTS
// ----------------------------
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging-compat.js");

// ----------------------------
// FIREBASE INIT
// ----------------------------
firebase.initializeApp({
  apiKey: "AIzaSyDYdFd-39KDzADVcfurLdT9_W2cAIChFRA",
  authDomain: "refugioanimal-b6096.firebaseapp.com",
  projectId: "refugioanimal-b6096",
  storageBucket: "refugioanimal-b6096.firebasestorage.app",
  messagingSenderId: "122878637057",
  appId: "1:122878637057:web:951a15ed750ce8fca27c00"
});

const messaging = firebase.messaging();

// ----------------------------
// 🔥 FCM BACKGROUND MESSAGE
// ----------------------------
messaging.onBackgroundMessage((payload) => {
  console.log("📌 Background FCM recibido:", payload);

  const title = payload.notification?.title || "Notificación";
  const options = {
    body: payload.notification?.body || "",
    icon: "/testdashboard1/resources/icon-512x512.png",
    data: { link: payload.fcmOptions?.link || "/" }
  };

  self.registration.showNotification(title, options);
});

// ----------------------------
// 🔥 FALLBACK PUSH EVENT
// ----------------------------
self.addEventListener("push", (event) => {
  console.log("🔔 Push recibido (fallback):", event);

  if (!event.data) return;

  let data = {};

  try {
    data = event.data.json();
  } catch (e) {
    console.warn("⚠ No se pudo leer JSON:", e);
  }

  const title = data.notification?.title || "Notificación";
  const options = {
    body: data.notification?.body || "",
    icon: "/testdashboard1/resources/icon-512x512.png",
    data: { link: data.fcmOptions?.link || "/" }
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// ----------------------------
// 🔥 CLICK EN NOTIFICACIÓN
// ----------------------------
self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  const url = event.notification.data.link;

  event.waitUntil(
    clients.openWindow(url)
  );
});
