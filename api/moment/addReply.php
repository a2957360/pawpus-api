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
    $momentId=$data['momentId'];
    $replyToReplyId=$data['replyToReplyId'];
    $userId=$data['userId'];
    $atUserId=$data['atUserId'];
    $replyTitle=$data['replyTitle'];
    $replyContent=$data['replyContent'];
    //  0:回复朋友圈;1:回复回复的人; 
    $replyType=$data['replyType'];

    $stmt = $pdo->prepare("INSERT INTO `replyTable`(`momentId`, `replyToReplyId`, `userId`, `atUserId`, `replyTitle`, `replyContent`, `replyType`)
                           VALUES ('$momentId','$replyToReplyId','$userId','$atUserId','$replyTitle','$replyContent','$replyType')");
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
