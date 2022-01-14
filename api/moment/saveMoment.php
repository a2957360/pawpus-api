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
    $targetId=$data['targetId'];
    $userId=$data['userId'];
    //  0:服务;1:商品;2:朋友圈 
    $targetType=2;

    $stmt = $pdo->prepare("INSERT INTO `savedTable`(`targetId`, `userId`, `targetType`)
                           VALUES ('$targetId','$userId','$targetType')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
        if($serviceId != 0){
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
