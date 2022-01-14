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
    $language=$data['language'];
    //查询
    $userId=$data['userId'];
    $deliverId=$data['deliverId'];

    $stmt = $pdo->prepare("SELECT * From `deliverTable` WHERE `deliverId` = '$deliverId'");
    $stmt->execute();
    if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $deliverPrice = $row['deliverPrice'];
    }
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }

    //解决id自增问题
    $stmt = $pdo->prepare("UPDATE `cartTable` SET 
                      `deliverPrice` = '$deliverPrice',`deliverId`='$deliverId'
                      WHERE `userId` = '$userId'");
    $stmt->execute();
    // if($stmt->rowCount() == 0){
    //     echo json_encode(["message"=>"error"]);
    //     exit();
    // }
    echo json_encode(["message"=>"success"]);
    exit();

  }
