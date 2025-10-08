var app = angular.module("refugioApp", ["ngRoute", "ngAnimate"]);

app.config(function($routeProvider) {
  $routeProvider
    .when("/", {
      templateUrl: "views/home.html"
    })
    .when("/mascotas", {
      templateUrl: "views/mascotas.html",
      controller: "MascotasCtrl"
    })
    .when("/padrinos", {
      templateUrl: "views/padrinos.html",
      controller: "PadrinosCtrl"
    })
    .when("/apoyos", {
      templateUrl: "views/apoyos.html",
      controller: "ApoyosCtrl"
    })
    .otherwise({ redirectTo: "/" });
});

/* --- MASCOTAS --- */
app.controller("MascotasCtrl", function($scope) {
  const STORAGE_KEY = "mascotas_refugio";
  $scope.mascotas = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

  // Si no hay datos, insertar un ejemplo inicial
  if ($scope.mascotas.length === 0) {
    $scope.mascotas.push({
      idMascota: 1,
      nombre: "Chaparro",
      sexo: "M",
      raza: null,
      peso: 10,
      condiciones: "Tiene un problema en el estómago, necesita 2 operaciones."
    });
    localStorage.setItem(STORAGE_KEY, JSON.stringify($scope.mascotas));
  }
});

/* --- PADRINOS --- */
app.controller("PadrinosCtrl", function($scope) {
  const STORAGE_KEY = "padrinos_refugio";
  $scope.padrinos = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
  $scope.nuevoPadrino = {};

  function guardarLocal() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify($scope.padrinos));
  }

  $scope.agregarPadrino = function() {
    if (
      !$scope.nuevoPadrino.nombrePadrino ||
      !$scope.nuevoPadrino.telefono ||
      !$scope.nuevoPadrino.correoElectronico
    ) return;

    const nuevo = {
      idPadrino: Date.now(),
      nombrePadrino: $scope.nuevoPadrino.nombrePadrino,
      sexo: $scope.nuevoPadrino.sexo || "",
      telefono: $scope.nuevoPadrino.telefono,
      correoElectronico: $scope.nuevoPadrino.correoElectronico
    };

    $scope.padrinos.push(nuevo);
    guardarLocal();
    $scope.nuevoPadrino = {};
  };

  $scope.eliminarPadrino = function(id) {
    $scope.padrinos = $scope.padrinos.filter(p => p.idPadrino !== id);
    guardarLocal();
  };
});

/* --- APOYOS --- */
app.controller("ApoyosCtrl", function($scope) {
  $scope.apoyos = [
    { idApoyo:1, idMascota:1, idPadrino:1, monto:1000.00, causa:"Pago de una operación.", mascota:"Chaparro", padrino:"José Pérez" },
    { idApoyo:2, idMascota:null, idPadrino:1, monto:500.00, causa:"Apoyo al refugio.", mascota:null, padrino:"José Pérez" }
  ];
});
