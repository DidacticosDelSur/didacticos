<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $path_home . 'includes/phpmailer/src/Exception.php';
require $path_home . 'includes/phpmailer/src/PHPMailer.php';
require $path_home . 'includes/phpmailer/src/SMTP.php';

//Load Composer's autoloader
require $path_home . 'vendor/autoload.php';


//Create an instance; passing `true` enables exceptions

function sendEmail($to, $subject, $preferred, $message, $bcc = null, $header = 'default')
{
    global $env;

    //$to        = "didacticos@yopmail.com";Parche para pruebas BORRAR AL PASAR A PRODUCCION
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();                                            //Send using SMTP
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        
        if ($env === 'production') {
            $mail->Host       = 'srt.websitewelcome.com';                     //Set the SMTP server to send through
            $mail->Username   = 'noreply@didacticosdelsur.com';                     //SMTP username
            $mail->Password   = 'hb4$_PR0Ts{$';                               //SMTP password
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        }
        else {
            //$mail->Host       = 'smtp.mailtrap.io';                     //Set the SMTP server to send through
            //$mail->Username   = '09ec3df61903fc';                     //SMTP username
            //$mail->Password   = '6ea4bf09c6f296';                               //SMTP password   
            //$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
   
            $mail->Host       = 'srt.websitewelcome.com';                     //Set the SMTP server to send through
            $mail->Username   = 'no-reply@diegofernandez.org';                     //SMTP username
            $mail->Password   = 'MguFkrO-%Kv,';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        }


        //Recipients
        //$mail->setFrom('no-reply@diegofernandez.org', 'Didácticos del Sur');
        $mail->setFrom('noreply@didacticosdelsur.com', 'Didácticos del Sur');
        $mail->addAddress($to);     //Add a recipient
        if (!empty($bcc)) {
            $mail->addBCC($bcc);     //Add a recipient
        }
        $mail->addReplyTo('info@didacticosdelsur.com', 'Didácticos del Sur');

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;

        $message .= '<br><br>Lo saludamos atentamente,';

        $content = file_get_contents(PATH . 'emails/index.html');

        $content = str_replace('{{Preferred}}', $preferred, $content);
        $content = str_replace('{{message}}', $message, $content);
        $content = str_replace('{{header}}', $header, $content);

        $mail->Body    = $content;
        $mail->AltBody = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }

    /*
    $headers = "MIME-Version: 1.0" . "\r\n" .   
                "Content-type:text/html;charset=UTF-8" . "\r\n" .
                'From: noreply@didacticosdelsur.com' . "\r\n" .
                'Reply-To: info@didacticosdelsur.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

    $message .= '<br><br>Lo saludamos atentamente,';

    $content = file_get_contents(PATH . 'emails/index.html');

    $content = str_replace('{{Preferred}}', $preferred, $content);
    $content = str_replace('{{message}}', $message, $content);

    return mail($to, $subject, $content, $headers);*/
}