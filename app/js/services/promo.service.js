(function () {
    "use strict";
    angular.module("app").factory("Promo", Promo);
    Promo.$inject = ["$http", "$q", "REST"];
    function Promo($http, $q, REST) {
        var dataFactory = {};
        dataFactory.getRanking = function () {
            var data = {metodo: "cargaRanking"};
            return $http.post(REST.API + "olimpiadasCtrl.php", data).then(complete).catch(failed);
            function complete(response) {
                return $q.when(response.data)
            }

            function failed(response) {
                return $q.reject(response.data)
            }
        };
        dataFactory.getmyRanking = function (idUser, tokenSession) {
            var data = {user: {usuario_id: idUser, token: tokenSession}, metodo: "rankingUsuario"};
            return $http.post(REST.API + "olimpiadasCtrl.php", data).then(complete).catch(failed);
            function complete(response) {
                return $q.when(response.data)
            }

            function failed(response) {
                return $q.reject(response.data)
            }
        };
        dataFactory.cargaRankingSemana = function () {
            var data = {metodo: "cargaRankingSemana"};
            return $http.post(REST.API + "olimpiadasCtrl.php", data).then(complete).catch(failed);
            function complete(response) {
                return $q.when(response.data)
            }

            function failed(response) {
                return $q.reject(response.data)
            }
        };
        dataFactory.ganadores = function () {
            var data = {metodo: "ganadores"};
            return $http.post(REST.API + "olimpiadasCtrl.php", data).then(complete).catch(failed);
            function complete(response) {
                return $q.when(response.data)
            }

            function failed(response) {
                return $q.reject(response.data)
            }
        };
        dataFactory.ganadores1 = function () {
            var data = {metodo: "ganadores1"};
            return $http.post(REST.API + "olimpiadasCtrl.php", data).then(complete).catch(failed);
            function complete(response) {
                return $q.when(response.data)
            }

            function failed(response) {
                return $q.reject(response.data)
            }
        };
        return dataFactory
    }
})();