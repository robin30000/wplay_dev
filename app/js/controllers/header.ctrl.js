/* eslint-disable indent */
(function () {
    'use strict';
    angular.module('app').controller('HeaderController', HeaderController);
    HeaderController.$inject = ['$route', '$rootScope', '$scope', '$location', '$uibModal', '$timeout', 'growl', 'AuthService', 'REST'];

    function HeaderController($route, $rootScope, $scope, $location, $uibModal, $timeout, growl, AuthService, REST) {
        var vm = this;
        var message = {};
        vm.activeMenu = 0;
        $rootScope.startClock = Date.now();
        vm.clock = vm.startClock;
        vm.tickInterval = 1000;
        vm.timeConnected = (new Date()).getTime() - $rootScope.time;
        init();

        function init() {
            $timeout(tick, vm.tickInterval);
        }

        function tick() {
            vm.clock = Date.now();
            vm.timeConnected = (new Date()).getTime() - $rootScope.time;
            if ($rootScope.authenticated) {
                $timeout(tick, vm.tickInterval);
            }
            if ((vm.clock - $rootScope.startClock) > (REST.TIMEOUT * 60 * 1000)) {
                AuthService.logout().then(function (data) {
                    $rootScope.authenticated = false;

                    growl.warning("Su sesión ha sido cerrada por inactividad", {
                        ttl: -1
                    }, function () {
                        window.location.reload();
                    });
                });
            }
        }

        vm.viewMenu = function viewMenu() {
            vm.activeMenu = !vm.activeMenu;
        };
        vm.getClass = function (path) {
            var url = $location.path() + '/';
            return (url.substr(0, url.indexOf('/', 1)) === path) ? 'active' : '';
        };
        /*vm.open = function () {
         var modalInstance = $uibModal.open({
         ariaLabelledBy: 'modal-title',
         ariaDescribedBy: 'modal-body',
         templateUrl: 'app/views/login.modal.html',
         controller: 'LoginController',
         controllerAs: 'login',
         size: 'sm'
         });
         modalInstance.result.then(function () {
         }, function () {
         console.log("failed");
         });
         };*/
        vm.video = function () {
            $location.path("/video");
        };

        vm.logout = function () {
            AuthService.logout($rootScope.id).then(complete).catch(failed);

            function complete(data) {
                if (data.state == 1) {
                    $rootScope.authenticated = false;
                    $location.path("/");
                }
            }

            function failed(data) {
                console.log(data);
            }
        };

        vm.viewMessages = function () {
            $location.path("cuenta-juego/mensajeria");
        };

        vm.getSaldo = function () {
            AuthService.getSaldo($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);

            function complete(data) {
                $scope.saldo = data.saldo;
                $scope.saldoBono = data.saldoBono;
                $scope.creditoParticipacion = data.creditoParticipacion;
            }

            function failed(data) {
                console.log(data);
            }
        };

        var login = {};
        login.inputType = 'password';
        login.showPassword = function () {
            if (vm.passwordCb) {
                vm.inputType = 'text';
            } else {
                vm.inputType = 'password';
            }
        };
        login.submit = function (user) {
            AuthService.login(user).then(complete).catch(failed);

            function complete(data) {
                if (data.state === 7) {
                    $rootScope.authenticated = true;
                    $rootScope.id = data.id;
                    $rootScope.identity = data.numDoc;
                    $rootScope.name = data.nombre;
                    $rootScope.time = data.timeOnline;
                    $rootScope.tokenSession = data.token_session;
                    growl.success(data.msg, {});
                    AuthService.getSaldo(data.id, data.token_session).then(function (data) {
                        $rootScope.saldo = data.saldo;
                        $rootScope.saldoBono = data.saldoBono;
                        $rootScope.creditoParticipacion = data.creditoParticipacion;
                    }).catch(function (data) {
                        console.log(data);
                    });
                    /*if (data.tipo_registro == 0) {
                        $location.path('/actualiza-registro');
                    } else if (data.pregunta_seguridad == 0) {
                        $location.path('/preguntas-seguridad');
                    }*/
                    $location.path('/apuestas');
                } else {
                    var config = {};
                    growl.error(data.msg, config);
                }
            }

            function failed(data) {
            }
        };
        login.forgetPassword = function () {
            $location.path('/recuperar-contrasena');
        };
        login.register = function () {
            $location.path('/registro');
        };

        vm.login = login;

    }

    angular.module('app').controller('LoginController', LoginController);
    LoginController.$inject = ['$rootScope', '$scope', '$uibModalInstance', '$location', 'growl', 'AuthService'];

    function LoginController($rootScope, $scope, $uibModalInstance, $location, growl, AuthService) {
        var vm = this;
        vm.inputType = 'password';
        vm.showPassword = function () {
            if (vm.passwordCb) {
                vm.inputType = 'text';
            } else {
                vm.inputType = 'password';
            }
        };
        vm.submit = function (user) {
            AuthService.login(user).then(complete).catch(failed);

            function complete(data) {
                if (data.state === 7) {
                    $rootScope.authenticated = true;
                    $rootScope.id = data.id;
                    $rootScope.identity = data.numDoc;
                    $rootScope.name = data.nombre;
                    $rootScope.time = data.timeOnline;
                    $rootScope.tokenSession = data.token_session;
                    growl.success(data.msg, {});
                    AuthService.getSaldo(data.id, data.token_session).then(function (data) {
                        $rootScope.saldo = data.saldo;
                        $rootScope.saldoBono = data.saldoBono;
                        $rootScope.creditoParticipacion = data.creditoParticipacion;
                    }).catch(function (data) {
                        console.log(data);
                    });
                    $uibModalInstance.close();
                    $location.path('/apuestas');
                } else {
                    var config = {};
                    growl.error(data.msg, config);
                }
            }

            function failed(data) {
            }
        };
        vm.forgetPassword = function () {
            $uibModalInstance.close();
            $location.path('/recuperar-contrasena');
        };
        vm.register = function () {
            $uibModalInstance.close();
            $location.path('/registro');
        };
    }

    angular.module('app').controller('MenuController', MenuController);
    MenuController.$inject = ['$scope', '$location'];

    function MenuController($scope, $location) {
        var vm = this;
        vm.active = false;
        vm.activeSubmenu = 0;
        vm.showMenu = function () {
            vm.active = !vm.active;
            if (!vm.active) {
                vm.activeSubmenu = 0;
            }
        };
        vm.getClass = function (path) {
            var url = $location.path() + '/';
            if (url.substr(0, path.length) === path) {
                return 'active';
            }
            return '';
        };
        vm.getClassMob = function (path, name) {
            var url = $location.path() + '/';
            if (url.substr(0, path.length) === path) {
                vm.currentItem = name;
                return 'cs-selected';
            }
            return '';
        };
        vm.showSubmenu = function (id) {
            if (vm.activeSubmenu === id) {
                vm.activeSubmenu = 0;
            } else {
                vm.activeSubmenu = id;
            }
        };
    }
})();
