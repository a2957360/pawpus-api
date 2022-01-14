<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
  //Server settings
  $mail->SMTPDebug = 0;                                 // Enable verbose debug output
  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'smtp.ionos.com';  // Specify main and backup SMTP servers
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = "invoice@kiwecanada.com";                 // SMTP username
  $mail->Password = 'Bowenkim123@';                           // SMTP password
  $mail->SMTPSecure = 'tls';                     // Enable TLS encryption, `ssl` also accepted
  $mail->Port = 587;                                    // TCP port to connect to
  $mail->CharSet="UTF-8";
  //Recipients
  $mail->setFrom('customercare@kiwe.ca', 'Kiwe');
  // $mail->addAddress($logname, 'guest');     // Add a recipient
  //Attachments
  //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
  //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
// try {
//   //Content
//   $mail->isHTML(true);                                  // Set email format to HTML
//   $mail->AddEmbeddedImage('static/img/icon.png','logo');
//   $mail->Subject = 'contact form';
//   $mail->Body    = "";


//   if($mail->send()){
//     echo "<script> location.href='passwordsuccess.html'; </script>";
//   }
//   } catch (Exception $e) {
//       echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
//   }
?>