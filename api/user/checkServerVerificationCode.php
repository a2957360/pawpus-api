<?php
include("../../include/sql.php");

http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

$data = file_get_contents('php://input');
$data = json_decode($data,true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $serverId=$data['serverId'];
  $serverPhone=$data['serverPhone'];
  $serverPhone=str_replace("-", " ", $serverPhone);
  $verificationCode=$data['verificationCode'];

  $stmt = $pdo->prepare("SELECT * From `textTable` WHERE `userPhone` = '$serverPhone' AND `textType` = '1';");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $code=$row['code'];
      $uploadTime=$row['uploadTime'];
    }
  }
  if($code == $verificationCode){
    // $stmt = $pdo->prepare("UPDATE `userTable` SET `userPhone` = '$serverPhone'
    //                       WHERE `userId` = '$serverId'");
    // $stmt->execute();
    // if($stmt->rowCount() > 0){
      echo json_encode(["message"=>"success"]);
      exit();
    // }else{
    //   echo json_encode(["message"=>"change error"]);
    //   exit();  
    // }
  }else{
    echo json_encode(["message"=>"not match","errorCode"=>108]);
    exit();  
  }
}
