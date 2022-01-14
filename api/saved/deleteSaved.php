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
    //savedId是保存的id
    $targetId=$data['targetId'];
    $userId=$data['userId'];
    //0:服务;1:商品;2:朋友圈 
    $targetType=$data['targetType'];

    $stmt = $pdo->prepare("DELETE FROM `savedTable` WHERE `targetId` = '$targetId' AND `userId` = '$userId' AND `targetType` = '$targetType'");
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
