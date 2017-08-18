(function () {
    "use strict";
    angular.module("app").controller("bancaController", bancaController);
    bancaController.$inject = ["$timeout", "$rootScope", "$scope", "$routeParams", "$location", "growl", "GameAccount", "$route"];
    function bancaController($timeout, $rootScope, $scope, $routeParams, $location, growl, GameAccount, $route) {
        var vm = this;
        vm.activeTab = $routeParams.paramId;
        vm.init = init();
        function init() {
            getProfile();
            getBanks();
            getTipoCuenta();
            getTipoCliente();
            getAccounts()
        }

        function getProfile() {
            GameAccount.getProfile($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);
            function complete(response) {
                var data = response.dato;
                vm.firstName = data.pNom;
                vm.secondName = data.sNom;
                vm.lastName = data.pApe;
                vm.secondLastname = data.sApe;
                vm.identity = parseInt(data.numDoc);
                vm.address = data.dir;
                vm.phone = data.fijo;
                vm.mobile = data.movil;
                vm.email = data.email
            }

            function failed(data) {
                console.log(data)
            }
        }

        function getBanks() {
            GameAccount.getBanks().then(complete).catch(failed);
            function complete(data) {
                vm.banks = data
            }

            function failed(data) {
                console.log(data)
            }
        }

        function getTipoCuenta() {
            vm.accountTypes = [{value: 1, option: "Ahorros"}, {value: 2, option: "Corriente"}]
        }

        function getTipoCliente() {
            vm.clientTypes = [{value: 1, option: "Persona"}, {value: 2, option: "Empresa"}]
        }

        function getAccounts() {
            var columnDefs = [{headerName: "Banco id", field: "id", visible: false}, {
                headerName: "Banco",
                field: "descripcion",
                cellStyle: {"text-align": "center"},
                width: "25%",
                minWidth: 70
            }, {
                headerName: "Tipo de Cuenta",
                field: "tipo_cuenta",
                cellStyle: {"text-align": "center"},
                width: "20%",
                minWidth: 70
            }, {
                headerName: "Número de Cuenta",
                field: "n_cuenta",
                cellStyle: {"text-align": "center"},
                width: "20%",
                minWidth: 70
            }, {
                headerName: "Estado",
                field: "estado",
                cellStyle: {"text-align": "center"},
                width: "18%",
                minWidth: 70
            }, {
                name: "Eliminar",
                cellTemplate: "<div><button ng-click='grid.appScope.delete(row)' class='btn btn-sm btn-wplay' ng-confirm-click='¿Esta seguro que desea eliminar esta cuenta bancaria?'>Eliminar cuenta</button></div>",
                width: "17%",
                minWidth: 70
            }];
            vm.gridOptions = {columnDefs: columnDefs};
            GameAccount.getBankAccount($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);
            function complete(data) {
                if (data.data.state == 1) {
                    vm.gridOptions.data = data.data.dato
                } else {
                    $timeout(function () {
                        growl.error(data.data.msg)
                    }, 3e3)
                }
            }

            function failed(data) {
            }
        }

        $scope.delete = function (row) {
            GameAccount.deleteBank($rootScope.id, $rootScope.tokenSession, row.entity.id).then(complete).catch(failed);
            function complete(data) {
                if (data.data.state == 1) {
                    growl.success(data.data.msg);
                    $timeout(function () {
                        $route.reload()
                    }, 3e3)
                } else {
                    growl.error(data.data.msg)
                }
            }

            function failed(data) {
            }
        };
        vm.backstep = function backstep() {
            vm.activeTab = vm.activeTab % 3 - 1;
            changePath(vm.activeTab)
        };
        vm.nextstep = function backstep() {
            vm.activeTab = vm.activeTab % 3 + 1;
            changePath(vm.activeTab)
        };
        vm.save = function save() {
            GameAccount.bankAccount($rootScope.id, $rootScope.tokenSession, vm.bank, vm.bankAccount, vm.accountType, vm.clientType).then(complete).catch(failed);
            function complete(data) {
                var config = {referenceId: 1};
                if (data.state == 1) {
                    growl.success(data.msg, config);
                    vm.activeTab = vm.activeTab % 3 + 1;
                    changePath(vm.activeTab)
                } else if (data.state == 99) {
                    $location.path("/")
                } else {
                    growl.error(data.msg, config)
                }
            }

            function failed(data) {
                console.log(data)
            }
        };
        vm.selectTab = function selectTab(tab) {
            vm.activeTab = tab;
            changePath(vm.activeTab)
        };
        function changePath(newId) {
            var urlPath = $location.path();
            urlPath = urlPath.substr(1, urlPath.lastIndexOf("/"));
            $location.path(urlPath + newId)
        }
    }

    angular.module("app").controller("bancadepositController", bancadepositController);
    bancadepositController.$inject = ["$rootScope", "$scope", "$uibModal", "GameAccount", "growl"];
    function bancadepositController($rootScope, $scope, $uibModal, GameAccount, growl) {
        var vm = this;
        vm.openPasarela = function (idPasarela) {
            GameAccount.getPasarela($rootScope.id, $rootScope.tokenSession, idPasarela, vm.quantity).then(complete).catch(failed);
            function complete(response) {
                if (response.state == 1) {
                    var data = response.usuario;
                    data.idPasarela = idPasarela;
                    var modalInstance = $uibModal.open({
                        ariaLabelledBy: "modal-title",
                        ariaDescribedBy: "modal-body",
                        templateUrl: "app/views/pasarela.modal.html",
                        controller: "pasarelaController",
                        controllerAs: "pasarela",
                        backdrop: "static",
                        size: "sm",
                        resolve: {
                            data: function () {
                                return data
                            }
                        }
                    });
                    modalInstance.result.then(function () {
                        console.log("ok")
                    }, function () {
                        console.log("failed")
                    })
                } else if (response.state == 99) {
                    $location.path("/");

                } else {
                    growl.error(response.msg);
                }
            }

            function failed(data) {
                console.log(data)
            }
        };
        vm.payment = function () {
        }
    }

    angular.module("app").controller("bancadraftController", bancadraftController);
    bancadraftController.$inject = ["$route", "$rootScope", "$scope", "$uibModal", "growl", "AuthService", "GameAccount"];
    function bancadraftController($route, $rootScope, $scope, $uibModal, growl, AuthService, GameAccount) {
        var vm = this;
        vm.paymentMethods = [{value: "6", option: "Efectivo"}, {value: "1", option: "Bancario"}];
        vm.cashMethods = [{value: "0", option: "Saldo premios"}, {value: "1", option: "Saldo créditos"}];
        init();
        function init() {
            getData();
            getGrid()
        }

        vm.submit = function (form) {
            GameAccount.setBankRetirement($rootScope.id, $rootScope.tokenSession, vm.paymentMethod, vm.account, vm.quantity, vm.question, vm.cashMethod).then(complete).catch(failed);
            function complete(data) {
                var config = {referenceId: 1};
                if (data.state == 1) {
                    var modalInstance = $uibModal.open({
                        ariaLabelledBy: "modal-title",
                        ariaDescribedBy: "modal-body",
                        backdrop: false,
                        templateUrl: "app/views/retirement.modal.html",
                        controller: "retirementController",
                        controllerAs: "retirement",
                        resolve: {
                            data: function () {
                                return data.html
                            }
                        }
                    });
                    modalInstance.result.then(function () {
                        reset(form);
                        getData();
                        getDataGrid();
                        updateBalance()
                    }, function () {
                        console.log("failed")
                    })
                } else {
                    if (data.state == 99) {
                        $location.path("/")
                    } else {
                        growl.error(data.msg, config)
                    }
                }
            }

            function failed(data) {
                console.log(data)
            }
        };
        function updateBalance() {
            var config = {referenceId: 1};
            AuthService.getSaldo($rootScope.id, $rootScope.tokenSession).then(function (data) {
                if (data.state == 1) {
                    $rootScope.saldo = data.saldo;
                    $rootScope.saldoBono = data.saldoBono;
                    $rootScope.messages = data.msg
                } else {
                    $location.path("/")
                }
            }).catch(function (data) {
                console.log(data)
            })
        }

        function reset(form) {
            if (form) {
                vm.paymentMethod = undefined;
                vm.account = undefined;
                vm.quantity = undefined;
                vm.question = undefined;
                form.$setPristine();
                form.$setUntouched()
            }
        }

        function getData() {
            GameAccount.getDataBancaDraft($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);
            function complete(data) {
                vm.balance = data.data;
                vm.balanceCredit = data.creditoBase;
                vm.accounts = data.cuentas;
                vm.question = data.pregunta
            }

            function failed(data) {
            }
        }

        function getDataGrid() {
            GameAccount.getDatagridBancaDraft($rootScope.id, $rootScope.tokenSession, "cuentaCobro").then(complete).catch(failed);
            function complete(data) {
                vm.gridOptions.api.setRowData(data);
                vm.gridOptions.api.sizeColumnsToFit()
            }

            function failed(data) {
            }
        }

        function getGrid() {
            var columnDefs = [{
                name: "No. Documento",
                field: "cuenta_id",
                suppressSizeToFit: true,
                enableColumnResizing: true,
                cellStyle: {"text-align": "center"}
            }, {
                name: "Fecha Generación",
                field: "fecha",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Valor bruto",
                field: "valor_bruto",
                cellFilter: 'currency:"$":0',
                cellStyle: {"text-align": "center"}
            }, {
                name: "Descuento",
                field: "valor_retencion",
                cellFilter: 'currency:"$":0',
                cellStyle: {"text-align": "center"}
            }, {
                name: "Valor neto",
                cellFilter: 'currency:"$":0',
                field: "valor",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Forma de pago",
                field: "tipo",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Tipo Saldo",
                field: "tipo_saldo",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Estado",
                field: "estado",
                cellStyle: {"text-align": "center"}
            }];
            vm.gridOptions = {columnDefs: columnDefs, enableColumnResizing: true};
            GameAccount.getDatagridBancaDraft($rootScope.id, $rootScope.tokenSession, "cuentaCobroAll").then(complete).catch(failed);
            function complete(data) {
                vm.gridOptions.data = data
            }

            function failed(data) {
            }
        }
    }

    angular.module("app").controller("retirementController", retirementController);
    retirementController.$inject = ["$rootScope", "$scope", "$uibModalInstance", "data", "$route"];
    function retirementController($rootScope, $scope, $uibModalInstance, data, $route) {
        var vm = this;
        vm.efecty = data.efecty;
        vm.reBank = data.reBank;
        vm.npAp = data.npAp;
        vm.msg = data.msg;
        vm.retencion = data.retencion;
        vm.code = data.Documento_N;
        vm.codeClave = data.clave;
        vm.paymentMethod = data.Medio_de_pago;
        vm.cedula = data.cedula;
        vm.name = data.Nombre;
        vm.date = data.Fecha;
        vm.quantityOrig = parseInt(data.quantityOrig);
        vm.porcentajeRetencion = data.porcentajeRetencion;
        vm.valorRetencion = data.valorRetencion;
        vm.clave = data.clave;
        vm.saldoType = data.saldoType;
        vm.pie_nota = data.pie_nota;
        vm.quantity = parseInt(data.Valor_retirar);
        vm.options = {
            height: 60,
            displayValue: false,
            font: "monospace",
            textAlign: "center",
            fontSize: 15,
            backgroundColor: "",
            lineColor: "#000000"
        };
        vm.printReceipt = function (printSectionId) {
            var innerContents = document.getElementById(printSectionId).innerHTML;
            var popupWinindow = window.open("", "_blank", "width=500,height=500,scrollbars=no,menubar=no,toolbar=no,location=no,status=no,titlebar=no");
            popupWinindow.document.open();
            popupWinindow.document.write('<html><head><link rel="stylesheet" type="text/css" href="/../app/css/style-print.css" /></head><body class="receipt" onload="window.print()">' + innerContents + "</html>");
            popupWinindow.document.close()
        };
        vm.close = function () {
            $uibModalInstance.close();
            $route.reload()
        }
    }

    angular.module("app").controller("bancaCancelDocController", bancaCancelDocController);
    bancaCancelDocController.$inject = ["$route", "$rootScope", "$scope", "$uibModal", "growl", "AuthService", "GameAccount", "$timeout"];
    function bancaCancelDocController($route, $rootScope, $scope, $uibModal, growl, AuthService, GameAccount, $timeout) {
        var vm = this;
        init();
        function init() {
            getGrid();
            getData()
        }

        function getData() {
            GameAccount.getDataBancaDraft($rootScope.id, $rootScope.tokenSession).then(complete).catch(failed);
            function complete(data) {
                vm.balance = data.data;
                vm.balanceCredit = data.creditoBase
            }

            function failed(data) {
            }
        }

        function getGrid() {
            var columnDefs = [{
                name: "N° Documento",
                field: "cuenta_id",
                suppressSizeToFit: true,
                enableColumnResizing: true,
                cellStyle: {"text-align": "center"}
            }, {
                name: "Fecha Generación",
                field: "fecha",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Valor bruto",
                field: "valor_bruto",
                cellFilter: 'currency:"$":0',
                cellStyle: {"text-align": "center"}
            }, {
                name: "Descuento",
                field: "valor_retencion",
                cellFilter: 'currency:"$":0',
                cellStyle: {"text-align": "center"}
            }, {
                name: "Valor neto",
                cellFilter: 'currency:"$":0',
                field: "valor",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Forma de pago",
                field: "tipo",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Tipo Saldo",
                field: "tipo_saldo",
                cellStyle: {"text-align": "center"}
            }, {
                name: "Estado",
                field: "estado",
                cellStyle: {"text-align": "center"}
            }];

            function rowTemplate() {
                return "<div ng-class=\"{ 'red':row.entity.aprobacion=='Requiere' }\">" + '<div ng-dblclick="grid.appScope.rowDblClick(row)" >' + '  <div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader }"  ui-grid-cell ></div></div>' + "</div>"
            }

            $scope.rowDblClick = function (row) {
                if (confirm("Esta seguro que desea cancelar el documento de retiro # " + row.entity.cuenta_id)) {
                    confirmDelete(row)
                }
            };
            function confirmDelete(row) {
                GameAccount.cancelDocBanca($rootScope.id, $rootScope.tokenSession, "cancelDocCuentaCobro", row.entity.cuenta_id).then(complete).catch(failed);
                function complete(data) {
                    if (data.state == 1) {
                        growl.success(data.msg);
                        $timeout(function () {
                            $route.reload()
                        }, 3e3)
                    } else {
                        growl.error(data.msg)
                    }
                }

                function failed(data) {
                }
            }

            vm.cgridOptions = {columnDefs: columnDefs, rowTemplate: rowTemplate()};
            GameAccount.getDatagridBancaDraft($rootScope.id, $rootScope.tokenSession, "cuentaCobro").then(complete).catch(failed);
            function complete(data) {
                vm.cgridOptions.data = data
            }

            function failed(data) {
            }
        }
    }

    angular.module("app").controller("bancaresponseController", bancaresponseController);
    bancaresponseController.$inject = ["$rootScope", "$scope", "$location", "AuthService", "GameAccount"];
    function bancaresponseController($rootScope, $scope, $location, AuthService, GameAccount) {
        var vm = this;
        init();
        function init() {
            var paramsObject = {};
            window.location.search.replace(/\?/, "").split("&").map(function (o) {
                paramsObject[o.split("=")[0]] = o.split("=")[1]
            });
            if (paramsObject.transactionId != undefined) {
                vm.transactionId = decodeURIComponent(paramsObject.transactionId);
                vm.description = decodeURIComponent(paramsObject.description);
                vm.referenceCode = decodeURIComponent(paramsObject.referenceCode);
                vm.value = paramsObject.TX_VALUE;
                vm.currency = paramsObject.currency;
                vm.lapPaymentMethod = decodeURIComponent(paramsObject.lapPaymentMethod);
                vm.buyerEmail = decodeURIComponent(paramsObject.buyerEmail);
                vm.date = paramsObject.processingDate;
                var state = getpaymentState(paramsObject.polResponseCode);
                vm.paymentState = state.state;
                vm.paymentStateDesc = state.msg
            }
        }

        function getpaymentState(id) {
            var states = [{ID: 1, state: "Aprobada", msg: "Transacción aprobada"}, {
                ID: 4,
                state: "Rechazada",
                msg: "Transacción rechazada por entidad financiera"
            }, {ID: 5, state: "Rechazada", msg: "Transacción rechazada por el banco"}, {
                ID: 6,
                state: "Rechazada",
                msg: "Fondos insuficientes"
            }, {ID: 7, state: "Rechazada", msg: "Tarjeta inválida"}, {
                ID: 8,
                state: "Rechazada",
                msg: "Contactar entidad financiera"
            }, {ID: 9, state: "Rechazada", msg: "Tarjeta vencida"}, {
                ID: 10,
                state: "Rechazada",
                msg: "Tarjeta restringida"
            }, {ID: 12, state: "Rechazada", msg: "Fecha de expiración o código de seguridadinválidos"}, {
                ID: 13,
                state: "Rechazada",
                msg: "Reintentar pago"
            }, {ID: 14, state: "Rechazada", msg: "Transacción inválida"}, {
                ID: 15,
                state: "Pendiente",
                msg: "Transacción en validación manual"
            }, {ID: 17, state: "Rechazada", msg: "El valor excede el máximo permitido por la entidad"}, {
                ID: 19,
                state: "Rechazada",
                msg: "Transacción abandonada por el pagador"
            }, {ID: 20, state: "Expirada", msg: "Transacción expirada"}, {
                ID: 22,
                state: "Rechazada",
                msg: "Tarjeta no autorizada para comprar por internet"
            }, {ID: 23, state: "Rechazada", msg: "Transacción rechazada por sospecha de fraude"}, {
                ID: 25,
                state: "Pendiente",
                msg: "Recibo de pago generado. En espera de pago"
            }, {ID: 26, state: "Pendiente", msg: "Recibo de pago generado. En espera de pago"}, {
                ID: 29,
                state: "Pendiente",
                msg: ""
            }, {ID: 9994, state: "Pendiente", msg: "En espera de confirmación de PSE"}, {
                ID: 9995,
                state: "Rechazada",
                msg: "Certificado digital no encotnrado"
            }, {
                ID: 9996,
                state: "Rechazada",
                msg: "No fue posible establecer comunicación con la entidad financiera"
            }, {ID: 9997, state: "Rechazada", msg: "Error comunicándose con la entidad financiera"}, {
                ID: 9998,
                state: "Pendiente",
                msg: "Transacción no permitida"
            }, {ID: 9999, state: "Rechazada", msg: "Error"}];
            var cont = 0;
            while (cont < states.length) {
                if (states[cont].ID == parseInt(id)) {
                    return states[cont]
                }
                ++cont
            }
            return {ID: null, state: null, msg: null}
        }
    }
})();