angular.module('app').config(routesConfig).run(runConfig);

function routesConfig($routeProvider, $locationProvider, $compileProvider, growlProvider) {
    $locationProvider.html5Mode(true).hashPrefix('!');
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(whatsapp|https|itms):/);
    growlProvider.globalTimeToLive(5000);
    growlProvider.globalDisableCountDown(true);
    $routeProvider.when('/', {
            title: 'Home',
            templateUrl: 'app/views/main.html',
            controller: 'homeController as home',
            resolve: {
                userData: loadUserData
            }
        }).when('/submenu/:paramId', {
            title: 'submenu',
            templateUrl: 'app/views/submenu_mobile.html',
            controller: 'submenuController as submenu',
            resolve: {
                userData: loadUserData
            }
        }).when('/registro', {
            title: 'register',
            templateUrl: 'app/views/register.html',
            controller: 'registerController as register',
            resolve: {
                userData: loadUserData
            }
        }).when('/registro/:segment', {
            title: 'register',
            templateUrl: 'app/views/register.html',
            controller: 'registerController as register',
            resolve: {
                userData: loadUserData
            }
        }).when('/actualiza-registro', {
            title: 'update_register',
            templateUrl: 'app/views/update_register.html',
            controller: 'updateController as update',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/actualiza-fecha', {
            title: 'update_birthDate',
            templateUrl: 'app/views/update_birthDate.html',
            controller: 'updateFechaController as date',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/localizacion', {
            title: 'location',
            templateUrl: 'app/views/location.html',
            controller: 'locationController as location',
            resolve: {
                userData: loadUserData
            }
        }).when('/preguntas-seguridad', {
            title: 'safety_questions',
            templateUrl: 'app/views/safety_questions.html',
            controller: 'questionsController as questions',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/mis-datos', {
            title: 'my_data',
            templateUrl: 'app/views/account/my_data.html',
            controller: 'myDataController as myData',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/cambiar-contrasena', {
            title: 'change_password',
            templateUrl: 'app/views/account/change_password.html',
            controller: 'changePasswordController as changePassword',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/registrar/:paramId', {
            title: 'banca',
            templateUrl: 'app/views/account/banca.html',
            controller: 'bancaController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/deposito', {
            title: 'banca',
            templateUrl: 'app/views/account/banca_deposit.html',
            controller: 'bancadepositController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/deposito1', {
            title: 'banca',
            templateUrl: 'app/views/account/banca_deposit1.html',
            controller: 'bancadepositController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/respuesta/payu', {
            title: 'banca',
            templateUrl: 'app/views/account/banca_response.html',
            controller: 'bancaresponseController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/respuesta/tu-compra', {
            title: 'banca',
            templateUrl: 'app/views/account/banca_response.html',
            controller: 'bancaresponseController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/retiro', {
            title: 'banca',
            templateUrl: 'app/views/account/banca_draft.html',
            controller: 'bancadraftController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/banca/cancela-doc-retiro', {
            title: 'banca',
            templateUrl: 'app/views/account/cancel_doc_retirement.html',
            controller: 'bancaCancelDocController as banca',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/autoexclusion/:paramId', {
            title: 'autoexclusion',
            templateUrl: 'app/views/account/autoexclusion.html',
            controller: 'exclusionController as exclusion',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/movimientos/:paramId', {
            title: 'movimientos',
            templateUrl: 'app/views/account/movement.html',
            controller: 'movementController as movement',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/cuenta-juego/mensajeria', {
            title: 'inbox',
            templateUrl: 'app/views/account/inbox.html',
            controller: 'inboxController as inbox',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/info/tarjetas-recargas', {
            title: 'recharge',
            templateUrl: 'app/views/info/recharge_card.html',
            controller: 'rechargeController as recharge',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/info/consulta-apuestas', {
            title: 'gettickets',
            templateUrl: 'app/views/info/get_tickets.html',
            controller: 'getTicketsController as getTickets',
            resolve: {
                userData: loadUserData
            },
            loginRequired: true
        }).when('/info/recargas-retiros', {
            title: 'rechargeInfo',
            templateUrl: 'app/views/info/recharge_withdrawal.html',
            controller: 'rechargeInfoController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/info/politicas', {
            title: 'gambler_protection',
            templateUrl: 'app/views/info/gambler_protection.html',
            controller: 'gamblerProtectionController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/info/realizar-apuestas', {
            title: 'howto_ticket',
            templateUrl: 'app/views/info/howtoTicket.html',
            controller: 'howtoTicketsController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/info/terminos-condiciones', {
            title: 'terms_conditions',
            templateUrl: 'app/views/info/terms_conditions.html',
            controller: 'termsConditionsController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/info/preguntas-frecuentes', {
            title: 'faq',
            templateUrl: 'app/views/info/faq.html',
            controller: 'faqController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/info/reglas-de-juego', {
            title: 'rules',
            templateUrl: 'app/views/info/rules.html',
            controller: 'rulesController as info',
            resolve: {
                userData: loadUserData
            }
        }).when('/apuestas', {
            title: 'bet',
            templateUrl: 'app/views/bet.html',
            controller: 'betController as bet',
            resolve: {
                userData: loadUserData
            }
        }).when('/apuestas-vivo', {
            title: 'livebet',
            templateUrl: 'app/views/livebet.html',
            controller: 'betController as bet',
            resolve: {
                userData: loadUserData
            }
        })
        /*.when('/contacto', {
                title: 'contact',
                templateUrl: 'app/views/contact.html',
                controller: 'contactController as contact',
                resolve: {
                    userData: loadUserData
                }
            })*/
        // .when('/trabaje-nosotros', {
        //     title: 'workwithUs',
        //     templateUrl: 'app/views/workwith_us.html',
        //     controller: 'workController as work',
        //     resolve: {
        //         userData: loadUserData
        //     }
        // })
        .when('/recuperar-contrasena', {
            title: 'forget-password',
            templateUrl: 'app/views/forget_password.html',
            controller: 'forgetPasswordController as forgetPassword',
            resolve: {
                userData: loadUserData
            }
        }).when('/restaura-contrasena/:mail', {
            title: 'restore-password',
            templateUrl: 'app/views/restore_password.html',
            controller: 'restorePasswordController as restorePassword',
            resolve: {
                userData: loadUserData
            }
        }).when('/verificar-cuenta/:mail', {
            title: 'verify_account',
            templateUrl: 'app/views/verify_account.html',
            controller: 'verifyAccountController as verifyAccount',
            resolve: {
                userData: loadUserData
            }
        }).when('/results', {
            title: 'results',
            templateUrl: 'app/views/results.html'
        }).when('/video', {
            title: 'video',
            templateUrl: 'app/views/video.html'
        }).when('/promo/:id', {
            redirectTo: function(obj, requestedPath) {
                window.location.href = "https://promo.wplay.co" + requestedPath;
            }
        }).when('/promo/registro', {
            redirectTo: function(obj, requestedPath) {
                window.location.href = "https://promo.wplay.co" + requestedPath;
            }
        }).otherwise({
            redirectTo: '/'
        });
}

function loadUserData($rootScope, $q, $route, $location, AuthService) {
    return AuthService.checkSession().then(complete, failed);

    function complete(data) {
        $rootScope.authenticated = true;
        $rootScope.id = data.usuario;
        $rootScope.tokenSession = data.tokenSession;
        $rootScope.name = data.nombre;
        $rootScope.identity = data.numDoc;
        $rootScope.time = data.timeOnline;
        $rootScope.tokenI = data.token_game;
        if (data.tipo_registro == 0) {
            if ($route.current.title != 'update_register') {
                $route.reload();
                $location.path('/actualiza-registro');
            }
        } else if (data.pregunta_seguridad == 0) {
            if ($route.current.title != 'safety_questions') {
                $route.reload();
                $location.path('/preguntas-seguridad');
            }
        } else if (data.c_fecha == 'S') {
            if ($route.current.title != 'update_birthDate') {
                $route.reload();
                $location.path('/actualiza-fecha');
            }
        } else {
            return data;
        }
    }

    function failed(reason) {
        $rootScope.authenticated = false;
        if ($route.current.loginRequired) {
            var error = {
                status: 401,
                message: "Unauthorized"
            };
            return $q.reject(error);
        }
    }
}

function runConfig($rootScope, $location, i18nService, growl, AuthService) {
    i18nService.setCurrentLang('es');
    $rootScope.$on('$routeChangeStart', function(e, curr, prev) {
        if (curr.$$route && curr.$$route.resolve) {
            $rootScope.loadingView = true;
        }
    });
    $rootScope.$on('$routeChangeSuccess', function(e, curr, prev) {
        $rootScope.loadingView = false;
        if ($rootScope.authenticated) {
            AuthService.getSaldo($rootScope.id, $rootScope.tokenSession).then(function(data) {
                var config = {
                    referenceId: 1
                };
                if (data.state == 1) {
                    $rootScope.saldo = data.saldo;
                    $rootScope.creditoParticipacion = data.creditoParticipacion;
                    $rootScope.saldoBono = data.saldoBono;
                    $rootScope.messages = data.msg;
                } else {
                    $location.path("/");
                    growl.error(data.msg + '', {
                        onclose: function() {
                            AuthService.logout().then(function(data) {
                                $rootScope.authenticated = false;
                            });
                        }
                    });
                }
            }).catch(function(data) {
                console.log(data);
            });
        }
    });
    $rootScope.$on('$routeChangeError', function(arg1, arg2, arg3, arg4) {
        if (arg4.status == 404) {
            $location.url('/');
        }
        if (arg4.status == 401) {
            $location.url('/');
        }
    });
}