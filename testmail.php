<?php
 include "includes/config.php";

include_once 'includes/functions/email.php';

const PATH = './html/';

$to        = "didacticos@yopmail.com";
//$bcc       = getEmailViajante($_SESSION['id_cliente']);
$subject   = "Pruebo envio de mails";
$preferred = "asd";
$message   = "Se ha realizado un pedido a Didácticos del Sur. El número de pedido es #, realizado el día 25/01/24<br>" .
" El destino del envío será asasasa<br><br>" .
" El total estimativo* es de $1452.-<br>" .
" * Los precios y el stock pueden variar sin previo aviso y están sujetos a cambios inflacionarios.<br>" .
  '<p style="font-weight: bold; color: red;">Atención: no debe abonar nada hasta que el pedido sea confirmado por un representante. Nos pondremos en contacto con Usted a la brevedad.</p>';

  sendEmail($to, $subject, $preferred, $message, null, 'pedido-confirmado');
 
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log('enviando mails');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/phpmailer/src/Exception.php';
require 'includes/phpmailer/src/PHPMailer.php';
require 'includes/phpmailer/src/SMTP.php';

//Load Composer's autoloader
require 'vendor/autoload.php';


$mail = new PHPMailer();

try {
  echo 'En el try';
 
  //Recipients
  //$mail->setFrom('no-reply@otamerica.com', 'Mailer');
//  $mail->addAddress('victoriaganuza@gmail.com', 'Joe User');     //Add a recipient
  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
  $mail->isSMTP();                                            //Send using SMTP
  $mail->Host       = 'srt.websitewelcome.com';                     //Set the SMTP server to send through
  $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
  $mail->Username   = 'no-reply@diegofernandez.org';                     //SMTP username
  $mail->Password   = 'MguFkrO-%Kv,';                               //SMTP password
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
  $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

  $mail->CharSet = 'utf-8';
  $mail->SMTPOptions = array(
    'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    )
  );
  $mail->setFrom('no-reply@diegofernandez.org', 'XXXX');
  $mail->addAddress('didacticos@yopmail.com', 'XXXX XXXX');
  $mail->addReplyTo('victoriaganuza@gmail.com', 'Didácticos del Sur');
  $mail->isHTML(true);
  $mail->Subject = 'Recuperación de contraseña';
  $mail->Body    = "Su nueva clave de acceso es XXX";
  $mail->send();
  echo "enviado";

} catch (Exception $e) {
  return "El mensaje no pudo ser enviado. Error: $mail->ErrorInfo";
}
*/