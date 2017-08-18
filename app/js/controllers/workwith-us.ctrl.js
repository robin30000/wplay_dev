(function () {
    "use strict";
    angular.module("app").controller("workController", workController);
    workController.$inject = ["$scope", "vcRecaptchaService", "MainService", "growl", "Utils", "REST"];
    function workController($scope, vcRecaptchaService, MainService, growl, Utils, REST) {
        var vm = this;
        vm.form = {};
        vm.response = null;
        vm.widgetId = null;
        vm.model = {key: REST.KEY};
        vm.setResponse = function (response) {
            vm.response = response
        };
        vm.setWidgetId = function (widgetId) {
            vm.widgetId = widgetId
        };
        vm.cbExpiration = function () {
            vcRecaptchaService.reload(vm.widgetId);
            vm.response = null
        };
        init();
        function init() {
            getDepartments()
        }

        function getDepartments() {
            Utils.getDepartment().then(function (data) {
                vm.departments = data
            }).catch(function (data) {
                console.log(data)
            })
        }

        vm.setDepartment = function () {
            Utils.getCityCode(vm.form.department).then(function (data) {
                vm.cities = data.ciudades
            }).catch(function (data) {
                console.log(data)
            })
        };

        function reset(form, data) {
            if (form) {
                data.name = undefined;
                data.lastname = undefined;
                data.email = undefined;
                data.identity = undefined;
                data.address = undefined;
                data.mobile = undefined;
                data.city = undefined;
                data.department = undefined;
                data.phone = undefined;
                data.body = undefined;
                vcRecaptchaService.reload(vm.widgetId);
                vm.response = null;
                form.$setPristine();
                form.$setUntouched()
            }
        }

        function validateCaptcha() {
            Utils.validateCaptcha(vm.form.captcha).then(complete).catch(failed);
            function complete(data) {

            }

            function failed(data) {
                console.log(data)
            }
        };

        vm.submit = function (form, formData) {
            var captcha = vcRecaptchaService.getResponse();
            MainService.workwithUs(formData, captcha).then(complete);
            function complete(data) {
                var config = {};
                if (data.state == 1) {
                    growl.success(data.msg, config);
                    reset(form, formData)
                } else {
                    growl.error(data.msg, config)
                }
            }
        }
    }
})();