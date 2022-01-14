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
    $savedId=$data['savedId'];
    $userId=$data['userId'];

    $stmt = $pdo->prepare("DELETE FROM `savedTable` WHERE `savedId` = '$savedId' AND `userId` = '$userId'");
    echo "DELETE FROM `savedTable` WHERE `savedId` = '$savedId' AND `userId` = '$userId'";
    $stmt->execute();
    if($stmt != null){
      $count = $stmt->rowCount();
      if($count > 0){
        echo json_encode(["message"=>"success"]);
        exit();
      }
      echo json_encode(["message"=>"error"]);
      exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
  }
