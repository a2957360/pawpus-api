<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  require_once "../sendemail.php";

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId=$data['userId'];
    $userPassword=$data['userPassword'];
    $reUserPassword=$data['reUserPassword'];
    if($userPassword != $reUserPassword){
      echo json_encode(["message"=>"password not match"]);
      exit();
    }
    $userPassword=password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE `userTable` SET `userPassword` = '$userPassword' WHERE `userId` = '$userId'");
    // $stmt = $pdo->prepare("UPDATE `userTable` SET `userPassword` = '$userPassword' WHERE `userId` = '$userId' AND `userState` != '0'");
    $stmt->execute();
    if($stmt->rowCount() > 0){
      //删除email
      $stmt = $pdo->prepare("DELETE From `emailTable` WHERE `userId`='$userId' AND `emailPurpose`='1'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
      exit();
    }else{
      echo json_encode(["message"=>"change error"]);
      exit();  
    }

  }
