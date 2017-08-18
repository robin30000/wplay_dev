(function () {
    "use strict";
    angular.module("app").controller("questionsController", questionsController);
    questionsController.$inject = ["$rootScope", "$scope", "AuthService", "$location", "$timeout", "growl"];

    function questionsController($rootScope, $scope, AuthService, $location, $timeout, growl) {
        var vm = this;
        init();

        function init() {
            getSafetyQuestions()
        }

        function getSafetyQuestions() {
            AuthService.getSafetyQuestions($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);

            function complete(response) {
                if (response.state == 1) {
                    vm.questionOne = response.data[0];
                    vm.questionTwo = response.data[1];
                    vm.questionThree = response.data[2]
                }
            }

            function failed(data) {
                console.log(data)
            }
        }

        vm.submit = function () {
            AuthService.setSafetyQuestions($rootScope.id, $rootScope.tokenSession, vm.questionOne, vm.questionTwo, vm.questionThree, $location).then(complete).catch(failed);

            function complete(response) {
                console.log(response);
                if (response.state == 1) {
                    growl.success(response.msg);
                    $timeout(function () {
                        $location.path("/apuestas")
                    }, 5e3)
                } else {
                    growl.error(response.msg)
                }
            }

            function failed(data) {
                console.log(data)
            }
        }
    }
})();