(function () {
  'use strict';

  angular.module('app').controller('exclusionController', exclusionController);

  exclusionController.$inject = [
    '$rootScope',
    '$scope',
    '$routeParams',
    '$location',
    'growl',
    'AuthService',
    'GameAccount'
  ];

  function exclusionController($rootScope, $scope, $routeParams, $location, growl, AuthService, GameAccount) {
    var vm = this;
    vm.activeTab = $routeParams.paramId;
    vm.items = ["Límite de Apuestas", "Límite tiempo de Juego", "Cancelar Cuenta"];
    vm.currentTitle = vm.items[vm.activeTab - 1];
    vm.cancel = [];

    vm.daybetLimit = null;
    vm.weekbetLimit = null;
    vm.monthbetLimit = null;

    vm.hasTimeLimit = false;
    vm.hasBetLimit = false;

    init();

    function init() {
      betLimit();
      timeLimit();
    }

    function betLimit() {

      vm.rangeDaybetLimit = [
        {
          value: '1',
          option: '5.000'
        }, {
          value: '2',
          option: '10.000'
        }, {
          value: '3',
          option: '20.000'
        }, {
          value: '4',
          option: '30.000'
        }, {
          value: '5',
          option: '40.000'
        }
      ];

      vm.rangeWeekbetLimit = [
        {
          value: '1',
          option: '50.000'
        }, {
          value: '2',
          option: '100.000'
        }, {
          value: '3',
          option: '200.000'
        }, {
          value: '4',
          option: '300.000'
        }, {
          value: '5',
          option: '400.000'
        }
      ];

      vm.rangeMonthbetLimit = [
        {
          value: '1',
          option: '500.000'
        }, {
          value: '2',
          option: '1.000.000'
        }, {
          value: '3',
          option: '5.000.000'
        }, {
          value: '4',
          option: '10.000.000'
        }, {
          value: '5',
          option: '15.000.000'
        }
      ];

      GameAccount.betLimit($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);

      function complete(data) {
        if (data.state == 1) {
          vm.daybetLimit = data.datos.limite_diario;
          vm.weekbetLimit = data.datos.limite_semanal;
          vm.monthbetLimit = data.datos.limite_mensual;
          vm.betLimitDate = data.datos.fecha_fin;
          vm.hasBetLimit = true;
        }
      }

      function failed(data) {}

    }

    function timeLimit() {
      vm.rangetimeLimit = [
        {
          value: '1',
          option: '1'
        }, {
          value: '3',
          option: '3'
        }, {
          value: '5',
          option: '5'
        }, {
          value: '10',
          option: '10'
        }, {
          value: '15',
          option: '15'
        }, {
          value: '30',
          option: '30'
        }
      ];

      GameAccount.timeLimit($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);

      function complete(data) {
        if (data.state === 1) {
          vm.hasTimeLimit = true;
          vm.timeLimit = data.datos.fecha_fin;
        }
      }

      function failed(data) {}
    }

    vm.savebetLimit = function savebetLimit() {
      GameAccount.updatebetLimit($rootScope.id, $rootScope.tokenSession, vm.betLimitPassword, vm.daybetLimit, vm.weekbetLimit, vm.monthbetLimit).then(complete).catch(failed);

      function complete(data) {
        var config = {
          referenceId: 1
        };
        if (data.state == 1) {
          growl.success(data.msg, config);
          betLimit();
        } else {
          if (data.state == 99) {
            $location.path("/");
          } else {
            growl.error(data.msg, config);
          }
        }
      }

      function failed(data) {}
    };

    vm.savetimeLimit = function savetimeLimit() {
      GameAccount.setTimeLimit($rootScope.id, $rootScope.tokenSession, vm.timeLimitPassword, vm.timeLimit).then(complete).catch(failed);

      function complete(data) {
        var config = {
          referenceId: 1
        };
        if (data.state == 1) {
          growl.success(data.msg, config);
          timeLimit();
        } else {
          if (data.state == 99) {
            $location.path("/");
          } else {
            growl.error(data.msg, config);
          }
        }
      }

      function failed(data) {}
    };

    vm.cancelAccount = function cancelAccount() {
      GameAccount.cancelAccount($rootScope.id, $rootScope.tokenSession, vm.cancel.password).then(complete).catch(failed);

      function complete(data) {
        var config = {
          referenceId: 1
        };
        if (data.state == 1) {
          growl.success(data.msg, {
            referenceId: 1,
            onclose: function () {
              AuthService.logout().then(function (data) {
                $rootScope.authenticated = false;
                $location.path("/");
              });
            }
          });
        } else {
          if (data.state == 99) {
            $location.path("/");
          } else {
            growl.error(data.msg, config);
          }
        }
      }

      function failed(data) {}
    };

    vm.selectTab = function selectTab(tab) {
      vm.activeTab = tab;
      changePath(tab);
      vm.currentTitle = vm.items[tab - 1];
    }

    function changePath(newId) {
      var urlPath = $location.path();
      urlPath = urlPath.substr(1, urlPath.lastIndexOf('/'));
      $location.path(urlPath + newId);
    }

  }
})();
