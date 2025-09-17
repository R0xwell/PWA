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

app.controller("MascotasCtrl", function($scope) {
  $scope.mascotas = [
    { idMascota:1, nombre:"Chaparro", sexo:"M", raza:null, peso:10, condiciones:"Tiene un problema en el estomago, necesita 2 operaciones." }
  ];
});

app.controller("PadrinosCtrl", function($scope) {
  $scope.padrinos = [
    { idPadrino:1, nombrePadrino:"Jose Perez", sexo:"M", telefono:"8621234567", correoElectronico:"jose.perez@gmail.com" }
  ];
});

app.controller("ApoyosCtrl", function($scope) {
  $scope.apoyos = [
    { idApoyo:1, idMascota:1, idPadrino:1, monto:1000.00, causa:"Pago de una operacion.", mascota:"Chaparro", padrino:"Jose Perez" },
    { idApoyo:2, idMascota:null, idPadrino:1, monto:500.00, causa:"Apoyo al refugio.", mascota:null, padrino:"Jose Perez" }
  ];
});
