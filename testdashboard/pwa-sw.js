function url(file) {
    return `/testdashboard/${(file ? file : "")}`
}

function syncNotifications(reg) {}
function periodicSyncNotifications(reg) {}
function sendOneNotification(reg, title, body) {
    if (Notification.permission !== "granted") {
        console.log("info")
        console.info("Sin permisos para enviar notificaciones.")
        return false
    }

    reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: "GENERA_TU_APPLICATION_SERVER_KEY"
    })
    .then(function (pushSubscription) {
        console.log("info")
        console.info("Yey!", pushSubscription)
        // The push subscription details needed by the application
        // server are now available, and can be sent to it using,
        // for example, an XMLHttpRequest.
        var data = new FormData();
        data.append("sub", JSON.stringify(pushSubscription))
        data.append("title", title)
        data.append("body", body)

        fetch(url("web-push-push-server.php"), {
            method: "POST",
            body: data
        })
        .then(function (res) {
            res.text()
        })
        .then(function (txt) {
            console.log("log")
            console.log(txt)
        })
        .catch(function (err) {
            console.log("error")
            console.error("Boo!", err)
        })
    })
    .catch(function (err) {
        // During development it often helps to log errors to the
        // console. In a production environment it might make sense to
        // also report information about errors back to the
        // application server.
        console.log("error")
        console.error("Boo!", err)
    })
}

var PRECACHENAME          = "dfhash-precache-v1"
var SYNCEVENTNAME         = "dfhash-sync-notifications"
var PERIODICSYNCEVENTNAME = "dfhash-periodic-sync-notifications"

var OFFLINEURL            = url("offline.html")

self.addEventListener("install", function (event) {
    console.log("info")
    console.info("Instalando...")

    // this happens while the old version is still in control
    event.waitUntil(
        caches.open(PRECACHENAME).then(function (cache) {
            console.log("info")
            console.info("Instalaci.n Completa")

            return cache.addAll([

                "https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-animate.min.js",
                "https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js",
                "https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js",

                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css",
                "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css",
                "https://code.jquery.com/jquery-3.7.1.min.js",

                url(),
                url("manifest.json"),
                url("?source=pwa"),
                url("resources/icono.ico"),
                url("resources/icono.png"),
                url("resources/icono-maskable.png"),

                OFFLINEURL,
                url("pwa-constants.js"),
                url("pwa-installer.js"),
                url("pwa-network.js"),
                // url("pwa-sw.js"),
                // url("pwa-i-loud.png"),
                // url("pwa-i-zap.png"),
            ])
        })
    )
})
self.addEventListener("activate", function (event) {
    // the old version is gone now, do what you couldn't
    // do while it was still around
    // event.waitUntil()
})
self.addEventListener("fetch", function (event) {
    event.respondWith(
        caches.match(event.request)
        .then(function (cachedResponse) {
            return cachedResponse || fetch(event.request).catch(async function (err) {
                // Only call event.respondWith() if this is a navigation request
                // for an HTML page.
                if (event.request.mode === "navigate") {
                    // catch is only triggered if an exception is thrown, which is
                    // likely due to a network error.
                    // If fetch() returns a valid HTTP response with a response code in
                    // the 4xx or 5xx range, the catch() will NOT be called.
                    console.log("error")
                    console.error("Fetch failed; returning offline page instead.", err)

                    var cache = await caches.open(PRECACHENAME)
                    var cachedResponse = await cache.match(OFFLINEURL)

                    return cachedResponse
                }
            })
        })
        .catch(function (err) {
            console.log("error")
            console.error("Boo!", err)
        })
    )
})
self.addEventListener("sync", function (event) {
    console.log("info")
	console.info("sync event", event)

    if (event.tag === SYNCEVENTNAME) {
        event.waitUntil(syncNotifications(registration))
    }
})
self.addEventListener("periodicsync", function (event) {
    console.log("info")
	console.info("periodic sync event", event)

    if (event.tag === PERIODICSYNCEVENTNAME) {
        event.waitUntil(periodicSyncNotifications(registration))
    }
})
self.addEventListener("push", function (event) {
    console.log("info")
    console.info(event.data)

    // From here we can write the data to IndexedDB, send it to any open
    // windows, display a notification, etc.
    var data = event.data.json()

    self.registration.showNotification(data.title, {
        body: data.body,
        icon: data.icon,
        image: data.image
    })
})
