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
    //userState 0:未激活;1：普通用户;2:服务者信息草稿;3:服务者待审核;4:服务者;5:黑名单
    $stmt = $pdo->prepare("UPDATE `userTable` SET 
                        `userState` = '3'
                        WHERE `userId` = '$userId' AND `userState` = '2'");
    $stmt->execute();
    if($stmt->rowCount() > 0){
      //修改服务状态
      // $stmt = $pdo->prepare("UPDATE `serviceTable` SET 
      //               `serviceState` = '0'
      //               WHERE `userId` = '$userId' AND `serviceState` = '-1'");
      // $stmt->execute();
      if($stmt->rowCount() > 0){
        echo json_encode(["message"=>"success"]);
        exit();
      }
    }
      echo json_encode(["message"=>"fail"]);
      exit();
  }
