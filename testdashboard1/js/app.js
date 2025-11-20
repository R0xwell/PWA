var app = angular.module("refugioApp", ["ngRoute", "ngAnimate"]);

app.config(function($routeProvider) {
  $routeProvider
    .when("/", { templateUrl: "views/home.html" })
    .when("/mascotas", { templateUrl: "views/mascotas.html", controller: "MascotasCtrl" })
    .when("/padrinos", { templateUrl: "views/padrinos.html", controller: "PadrinosCtrl" })
    .when("/apoyos", { templateUrl: "views/apoyos.html", controller: "ApoyosCtrl" })
    .when("/notifications", { templateUrl: "views/notifications.html", controller: "NotificationsCtrl" })
    .otherwise({ redirectTo: "/" });
});

/* ------------------ SERVICIO INDEXEDDB ------------------ */
app.factory("DBService", function($q) {
  let db;
  const dbName = "RefugioDB";
  const version = 1;

  const openDB = () => {
    const deferred = $q.defer();
    const request = indexedDB.open(dbName, version);

    request.onupgradeneeded = function(e) {
      db = e.target.result;
      ["mascotas","padrinos","apoyos","notifications"].forEach(store => {
        if(!db.objectStoreNames.contains(store)){
          let keyPath = store==="mascotas"?"idMascota": store==="padrinos"?"idPadrino": store==="apoyos"?"idApoyo":"idNotificacion";
          db.createObjectStore(store, { keyPath });
        }
      });
    };

    request.onsuccess = e => { db = e.target.result; deferred.resolve(db); };
    request.onerror = e => deferred.reject("Error al abrir IndexedDB: " + e.target.errorCode);

    return deferred.promise;
  };

  const getAll = (storeName) => {
    const deferred = $q.defer();
    openDB().then(() => {
      const tx = db.transaction(storeName, "readonly");
      const store = tx.objectStore(storeName);
      const req = store.getAll();
      req.onsuccess = () => deferred.resolve(req.result || []);
      req.onerror = e => deferred.reject(e);
    });
    return deferred.promise;
  };

  const save = (storeName, item) => {
    return openDB().then(() => new Promise((resolve,reject)=>{
      const tx = db.transaction(storeName,"readwrite");
      const store = tx.objectStore(storeName);
      const req = store.put(item);
      req.onsuccess = ()=>resolve(true);
      req.onerror = e=>reject(e);
    }));
  };

  const remove = (storeName, key) => {
    return openDB().then(() => new Promise((resolve,reject)=>{
      const tx = db.transaction(storeName,"readwrite");
      const store = tx.objectStore(storeName);
      const req = store.delete(key);
      req.onsuccess = ()=>resolve(true);
      req.onerror = e=>reject(e);
    }));
  };

  return { getAll, save, remove };
});

/* ------------------ UTILIDAD PARA EVITAR DUPLICADOS ------------------ */
function sanitizeList(lista, key) {
  const seen = new Set();
  const cleaned = [];
  lista.forEach(item => {
    if(!item[key]) item[key] = Date.now() + Math.random();
    if(!seen.has(item[key])){
      seen.add(item[key]);
      cleaned.push(item);
    }
  });
  return cleaned;
}

/* ------------------ CONTROLADORES ------------------ */

// 🐶 MASCOTAS
app.controller("MascotasCtrl", function($scope, DBService) {
  $scope.mascotas = [];

  DBService.getAll("mascotas").then(data=>{
    $scope.$apply(()=>{ $scope.mascotas = sanitizeList(data,"idMascota"); });
  });

  $scope.agregarMascota = function(){
    if(!$scope.nuevaMascota || !$scope.nuevaMascota.nombre) return;
    const nueva = { idMascota: Date.now(), nombre: $scope.nuevaMascota.nombre, sexo:$scope.nuevaMascota.sexo||"", raza:$scope.nuevaMascota.raza||"", peso:$scope.nuevaMascota.peso||0, condiciones:$scope.nuevaMascota.condiciones||"" };
    DBService.save("mascotas", nueva).then(()=>{
      $scope.$apply(()=>{ $scope.mascotas.push(nueva); });
    });
    $scope.nuevaMascota = {};
  };
});

// 💚 PADRINOS
app.controller("PadrinosCtrl", function($scope, DBService) {
  $scope.padrinos = [];

  DBService.getAll("padrinos").then(data=>{
    $scope.$apply(()=>{ $scope.padrinos = sanitizeList(data,"idPadrino"); });
  });

  $scope.agregarPadrino = function(){
    if(!$scope.nuevoPadrino || !$scope.nuevoPadrino.nombrePadrino) return;
    const nuevo = { idPadrino: Date.now(), nombrePadrino:$scope.nuevoPadrino.nombrePadrino, sexo:$scope.nuevoPadrino.sexo||"", telefono:$scope.nuevoPadrino.telefono||"", correoElectronico:$scope.nuevoPadrino.correoElectronico||"" };
    DBService.save("padrinos", nuevo).then(()=>{
      $scope.$apply(()=>{ $scope.padrinos.push(nuevo); });
    });
    $scope.nuevoPadrino={};
  };

  $scope.eliminarPadrino = function(id){
    DBService.remove("padrinos",id).then(()=>{
      $scope.$apply(()=>{ $scope.padrinos = $scope.padrinos.filter(p=>p.idPadrino!==id); });
    });
  };
});

// 🤝 APOYOS
app.controller("ApoyosCtrl", function($scope, DBService){
  $scope.apoyos = [];

  DBService.getAll("apoyos").then(data=>{
    $scope.$apply(()=>{ $scope.apoyos = sanitizeList(data,"idApoyo"); });
  });

  $scope.agregarApoyo=function(){
    if(!$scope.nuevoApoyo || !$scope.nuevoApoyo.monto) return;
    const nuevo = { idApoyo:Date.now(), idMascota:$scope.nuevoApoyo.idMascota||null, idPadrino:$scope.nuevoApoyo.idPadrino||null, monto:parseFloat($scope.nuevoApoyo.monto)||0, causa:$scope.nuevoApoyo.causa||"" };
    DBService.save("apoyos",nuevo).then(()=>{ $scope.$apply(()=>{ $scope.apoyos.push(nuevo); }); });
    $scope.nuevoApoyo={};
  };

  $scope.eliminarApoyo=function(id){
    DBService.remove("apoyos",id).then(()=>{ $scope.$apply(()=>{ $scope.apoyos=$scope.apoyos.filter(a=>a.idApoyo!==id); }); });
  };
});

// 🔔 NOTIFICACIONES
app.controller("NotificationsCtrl", function($scope, DBService){
  $scope.notifications = [];

  DBService.getAll("notifications").then(data=>{
    $scope.$apply(()=>{ $scope.notifications=sanitizeList(data,"idNotificacion"); });
  });
});
