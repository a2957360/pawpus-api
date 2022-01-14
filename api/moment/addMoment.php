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
    $momentImage = json_encode($data['momentImage'],JSON_UNESCAPED_UNICODE);
    $momentTitle=$data['momentTitle'];
    $momentContent=$data['momentContent'];
    //  0:发布;1:隐藏;  
    $momentType='0';

    //serviceType 0:寄样;1:日托;2:遛狗  
    $stmt = $pdo->prepare("INSERT INTO `momentTable`(`userId`, `momentImage`, `momentTitle`, `momentContent`, `momentType`)
                           VALUES ('$userId','$momentImage','$momentTitle','$momentContent','$momentType')");
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
