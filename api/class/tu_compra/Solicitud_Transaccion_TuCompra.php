<?php

session_start();
error_reporting(0);
date_default_timezone_set('America/Bogota');

$path = '../';

require_once $path . 'requires/global.php';

if (!$_SESSION['logueado'] AND !$_SESSION["win_perfil"] == "U") {
    header("Location: logout");
}

$fechaGuardada = $_SESSION["online"];
$ahora = date("Y-n-j H:i:s");
$tiempo_transcurrido = (strtotime($ahora) - strtotime($fechaGuardada));

if ($tiempo_transcurrido >= $tiempo_session) {
    echo '<script language=javascript>
            alert("Su session se ha cerrado por inactividad durante los últimos 20 minutos, Por favor ingrese nuevamente.")
            self.location = "logout";
        </script>';
} else {
    $_SESSION["online"] = $ahora;
}

$CurrentFileName = basename(__FILE__, '.php');

$tittle = EMPRESA . " | Deposito";

$css_files = array();

$js_files = array("<script src='" . $path . "libs/js/app.js'></script>");

$ng = "ng-app='wplay'";

$valida = @$_POST['compra'];

require_once $path . "template/header.php";

?>
<div class="container container-general">
    <div class="row">
        <ul class="nav nav-tabs">
            <li><a href="perfil-usuario" id="2" class="cuenta_usuario">Mis datos</a></li>
            <li><a href="cambiar-contrasena" id="3" class="cuenta_usuario">Cambiar Contraseña</a></li>
            <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Banca
                    <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="registro-cuenta" id="3" class="cuenta_usuario">Registrar Cuenta Bancaria</a></li>
                    <li class="active"><a href="javascript:void(0)" id="1" class="cuenta_usuario">Realizar Deposito</a>
                    </li>
                    <li><a href="cuenta_cobro" id="1" class="cuenta_usuario">Generar Documentos de Retiro</a></li>
                </ul>
            </li>
            <li><a href="autoexclusion" id="5" class="cuenta_usuario">AutoExclusion</a></li>
            <li><a href="gestion_cuenta" id="5" class="cuenta_usuario">Movimientos</a></li>
            <li><a href="mensajes" id="5" class="cuenta_usuario">Mensajería</a></li>

        </ul>
    </div>

    <div class="page-header">
        <h4>Depósitos
            <small>Ingrese la cantidad de dinero que desea depositar</small>
        </h4>
    </div>

    <div class="row" ng-controller="cargaTuCompra">
        <div class="col-sm-6 col-sm-offset-5"
             ng-init="carga_tu_compra('<?php echo $_SESSION["usuario"] ?>', '<?php echo $_SESSION["token_session"] ?>')">
            <form method="post" action="../controllers/pasarelaDePagosController" name="tuCompra">
                <div class="form-group">
                    <label class="sr-only" for="">valor</label>
                    <input type="text" class="form-control" name="valor" id="valor"
                           placeholder="Ingrese el valor a depositar" required>
                    <input type="hidden" name="operation" value="1">
                    <input type="hidden" name="movil" id="movil" ng-value="user.movil">
                    <input type="hidden" name="telefono" id="telefono" ng-value="user.telefono">
                    <input type="hidden" name="mail" id="mail" ng-value="user.mail">
                    <input type="hidden" name="apellido" id="apellido" ng-value="user.apellido">
                    <input type="hidden" name="nombre" id="nombre" ng-value="user.nombre">
                    <input type="hidden" name="documento" id="documento" ng-value="user.documento">
                    <input type="hidden" name="pais" id="pais" ng-value="user.pais">
                    <input type="hidden" name="ciudad" id="ciudad" ng-value="user.ciudad">
                    <input type="hidden" name="direccion" id="direccion" ng-value="user.direccion">
                    <input type="hidden" name="usuario_conectado_id" ng-value="user.usuario_id">
                </div>

                <!--<button type="submit" class="btn btn-primary" ng-disabled="!tuCompra.$valid"><img src="../images/encabezado.png" alt=""></button>-->
                <div class="row">
                    <div class="col-sm-8">
                        <button class="btn btn-default" type="submit">
                            <img src="../images/encabezado.png" alt="" width="auto" height="40">
                        </button>
                    </div>
                    <div class="col-sm-8">
                        <button class="btn btn-default" type="button" disabled>
                            <img src="../images/payuu.png" alt="" width="auto" height="40">
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script !src="">
    $("#valor").on('keyup', function () {
        var n = parseInt($(this).val().replace(/\D/g, ''), 10);
        $(this).val(n.toLocaleString());
    });
</script>


<?php

require_once $path . 'template/footer.php';

?>
