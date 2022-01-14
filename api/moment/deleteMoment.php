<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId=$data['userId'];
    $momentId=$data['momentId'];

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("DELETE FROM `momentTable` WHERE `userId` = '$userId' AND `momentId` = '$momentId'");
    $stmt->execute();
    if($stmt != null){
        $number = $stmt->rowCount();
        if($number != 0){
          $stmt = $pdo->prepare("DELETE FROM `replyTable` WHERE `momentId` = '$momentId'");
          $stmt->execute();
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
