<?php
  include("include/sql.php");
  require_once "sendemail.php";
  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
      $mail->addAddress($userEmail);     // Add a recipient
      // $mail->addAddress($cleanerEmail);     // Add a recipient
      $mail->isHTML(true);                                  // Set email format to HTML
      // $mail->AddEmbeddedImage('static/img/icon.png','logo');
      $mail->Subject = 'PawPus';
      // $mail->AddEmbeddedImage('include/image/1597326264.jpg','bg');
      $mail->Body = "";
      if($mail->send()){
      }
    }catch (Exception $e) {

    }
  }