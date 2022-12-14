(function () {
  'use strict';

  angular
    .module('app')
    .factory('GameAccount', GameAccount);

  GameAccount.$inject = [
    '$http',
    '$q',
    'REST'
  ];

  function GameAccount($http, $q, REST) {
    var dataFactory = {};

    dataFactory.getProfile = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'perfil'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.setProfile = function (user, tokenSession) {
      var data = {
        data: {
          id: user.id,
          token_session: tokenSession,
          direccion: user.address,
          fijo: user.tel,
          mobile: user.mobile,
          depart_res: {
            depto_id: user.department
          },
          ciudad_res: {
            ciudad_id: user.city
          },
          codPostal: {
            id_codigo_postal: user.zipcode
          }
        },
        metodo: 'guardaUpdatePerfil'
      }
      console.log(data);
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getIncome = function (idUser, tokenSession) {
      var data = {
        user: idUser,
        token: tokenSession,
        opcion: 'ajusteEntrada'
      }
      return $http.post(REST.API + 'movimientoController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getBalanceDetail = function (idUser, tokenSession) {
      var data = {
        user: idUser,
        token: tokenSession,
        opcion: 'saldoDiscriminado'
      }
      return $http.post(REST.API + 'movimientoController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getDataGrid = function (idUser, tokenSession, option) {
      var data = {
        user: idUser,
        token: tokenSession,
        opcion: option
      }
      return $http.post(REST.API + 'movimientoController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getDatagridBancaDraft = function (idUser, tokenSession, option) {
      var data = {
        user: idUser,
        token: tokenSession,
        opcion: option
      }
      return $http.post(REST.API + 'cuentaCobroController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getBanks = function (idUser, tokenSession) {
      var data = {
        metodo: 'bancos'
      }
      return $http.post(REST.API + 'bancosControllers.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.bankAccount = function (idUser, tokenSession, bank, bankAccount, accountType, clientType) {
      var data = {
        user: {
          id: idUser,
          token_session: tokenSession,
          cBancaria: bankAccount,
          banco: bank,
          tCuenta: {
            option: accountType
          },
          conTCliente: {
            option: clientType
          }
        },
        metodo: 'newCuenta'
      }
      return $http.post(REST.API + 'bancosControllers.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getBankAccount = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'loadDate'
      }
      return $http.post(REST.API + 'bancosControllers.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getDataBancaDraft = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'retiros_cuentas_bancarias'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.setBankRetirement = function (idUser, tokenSession, paymentMethod, accountNumber, quantity, question) {
      var data = {
        user: {
          id: idUser,
          token: tokenSession,
          valor: quantity,
          cuenta: accountNumber,
          formaPagos: {
            value: paymentMethod
          },
          preguntaSeguridad: question.id,
          pregunta: question.answer
        },
        metodo: 'cuentaCobroGuardad'
      }
      return $http.post(REST.API + 'cuentaCobro.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.betLimit = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'verLimiteApuesta'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.updatebetLimit = function (idUser, tokenSession, password, daybetLimit, weekbetLimit, monthbetLimit) {
      var data = {
        user: {
          user_id: idUser,
          token: tokenSession,
          contrasena: password,
          limiteDiarioApuestas: {
            valor: daybetLimit
          },
          limiteSemanalApuestas: {
            valor: weekbetLimit
          },
          limiteMensualApuestas: {
            valor: monthbetLimit
          }
        },
        metodo: 'autoExclusionApuestas'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.timeLimit = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'verLimiteTiempo'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.setTimeLimit = function (idUser, tokenSession, password, timeLimit) {
      var data = {
        user: {
          user_id: idUser,
          token: tokenSession,
          contrasena: password,
          limiteDias: {
            value: timeLimit
          }
        },
        metodo: 'autoExclusionTiempo'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.cancelAccount = function (idUser, tokenSession, password) {
      var data = {
        user: {
          user_id: idUser,
          token: tokenSession,
          contrasena: password
        },
        metodo: 'cancelarCuenta'
      }
      return $http.post(REST.API + 'userController.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.getMessages = function (idUser, tokenSession) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        metodo: 'traer_mensajes'
      }
      return $http.post(REST.API + 'mensajes.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    dataFactory.readMessage = function (idUser, tokenSession, idMessage) {
      var data = {
        user_id: idUser,
        token: tokenSession,
        message_id: idMessage,
        metodo: 'leer_mensaje'
      }
      return $http.post(REST.API + 'mensajes.php', data)
        .then(complete)
        .catch(failed);

      function complete(response) {
        return $q.when(response.data);
      }

      function failed(response) {
        return $q.reject(response.data);
      }
    };

    return dataFactory;
  }
})();
