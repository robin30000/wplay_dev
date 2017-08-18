<?php
include('../class.phpmailer.php');
include("../class.smtp.php");
/*////include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
define('GUSER', 'info@wplay.co'); // GMail username
define('GPWD', 'Infowplay2017*'); // GMail password

function smtpmailer($to, $from, $from_name, $subject, $body)
{
    global $error;
    $mail = new PHPMailer(true);  // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = false;  // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true;  // authentication enabled
    $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->Username = GUSER;
    $mail->Password = GPWD;
    $mail->SetFrom($from, $from_name);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AddAddress($to);
    if (!$mail->Send()) {
        $error = 'Mail error: ' . $mail->ErrorInfo;
        return false;
    } else {
        $error = 'Message sent!';
        return true;
    }
}

if (smtpmailer('robin_3x@hotmail.com', 'racastro218@mail.com', 'robinson castro', 'test mail message', 'Hello World!')) {
    echo 'porfin ';
}
if (!empty($error)) echo $error;*/

/**
 * Clase email que se extiende de PHPMailer
 */
class email extends PHPMailer
{

    //datos de remitente
    var $tu_email = 'info@wplay.co';
    var $tu_nombre = 'WPlay';
    var $tu_password = 'Infowplay2017*';

    /**
     * Constructor de clase
     */
    public function __construct()
    {
        //configuracion general
        $this->IsSMTP(); // protocolo de transferencia de correo
        $this->Host = 'smtp.gmail.com';  // Servidor GMAIL
        $this->Port = 587; //puerto
        $this->SMTPAuth = true; // Habilitar la autenticación SMTP
        $this->Username = $this->tu_email;
        $this->Password = $this->tu_password;
        $this->SMTPSecure = 'tls';  //habilita la encriptacion SSL
        //remitente
        $this->From = $this->tu_email;
        $this->FromName = $this->tu_nombre;
    }

    /**
     * Metodo encargado del envio del e-mail
     */
    public function enviar($para, $nombre, $titulo, $contenido)
    {
        $this->AddAddress($para, $nombre);  // Correo y nombre a quien se envia
        $this->WordWrap = 50; // Ajuste de texto
        $this->IsHTML(true); //establece formato HTML para el contenido
        $this->Subject = $titulo;
        $this->Body = $contenido; //contenido con etiquetas HTML
        $this->AltBody = strip_tags($contenido); //Contenido para servidores que no aceptan HTML
        //envio de e-mail y retorno de resultado
        return $this->Send();
    }

}//--> fin clase

/* == se emplea la clase email == */

$contenido_html = '<p>Hola, me llamo <em><strong>jc-mouse</strong></em> y quiero hacer una pregunta. </p>
<p>&iquest;POR QUE QUEREIS MATAR A BIN LADEN, SI &quot;OS<em><strong>AMA</strong></em>&quot; ?</p>
<p><strong>:)</strong></p>';

$email = new email();
if ($email->enviar('robin_3x@hotmail.com', 'Barack Obama', 'Tengo una pregunta', $contenido_html))
    echo 'Mensaje enviado';
else {
    echo 'El mensaje no se pudo enviar ';
    $email->ErrorInfo;
}


/*$mail             = new PHPMailer();
//$body             = file_get_contents('contents.html');
//$body             = eregi_replace("[\]",'',$body);
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = "smtp.gmail.com"; // SMTP server
$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
// 1 = errors and messages
// 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
$mail->Username = "info@wplay.co";
$mail->Password =  "Infowplay2017*";
$mail->SetFrom('name@yourdomain.com', 'First Last');
$mail->AddReplyTo("name@yourdomain.com","First Last");
$mail->Subject    = "PHPMailer Test Subject via smtp (Gmail), basic";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
$mail->MsgHTML('hola');
$address = "robin_3x@hotmail.com";
$mail->AddAddress($address, "John Doe");
//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {

echo "Mailer Error: " . $mail->ErrorInfo;
} else {

echo "Message sent!";
}*/

/*$mail = new PHPMailer(); // create a new object
$mail->IsSMTP(); // enable SMTP
$mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 587; // or 5871
$mail->IsHTML(true);
$mail->Username = "info@wplay.co";
$mail->Password =  "Infowplay*";
$mail->SetFrom("info@wplay.co");
$mail->Subject = "Test";
$mail->Body = "hello";
$mail->AddAddress("robin_3x@hotmail.com");

if(!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message has been sent opo";
}*/

// function EnviarCorreo($c_address,$c_from,$c_fromname,$c_subject,$c_include,$c_mensaje,$c_dominio,$c_compania,$c_color)
// {
//  //Crea las instancias y el cuerpo del correo
//  $mail = new PHPMailer() ;
//  $mail->IsSMTP();
//  $mail->Host   = "localhost";
//  $mail->SMTPDebug = 0;
//  $mail->From      = $c_from;
//  $mail->FromName  = $c_fromname;
//  $mail->Subject   = $c_subject;
//  ob_start();
//  $correo_mensaje = $c_mensaje;
//  $correo_dominio = $c_dominio;
//  $correo_compania = $c_compania;
//  $correo_color = $c_color;
//  include($c_include);
//  $message = ob_get_contents();
//  ob_end_clean();
//  $mail->msgHTML($message, dirname(__FILE__));
//  $mail->AddAddress($c_address, $c_address);
//  $mail->SMTPAuth = false;

//  //Verifica si el correo se envió satisfactoriamente
//  $enviado = false;
//  if($mail->Send())
//   $enviado = true;

//  //Retorna la respuesta
//  return $enviado;
// }