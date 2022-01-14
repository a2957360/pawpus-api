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
    $userName=$data['userName'];
    $userImage=$data['userImage'];

    $stmt = $pdo->prepare("UPDATE `userTable` SET 
                          `userName` = '$userName',`userImage` = '$userImage'
                          WHERE `userId` = '$userId' AND `userState` != '0'");
    $stmt->execute();
    if($stmt->rowCount() > 0){
      echo json_encode(["message"=>"success"]);
      exit();
    }else{
      echo json_encode(["message"=>"change error"]);
      exit();  
    }

  }
