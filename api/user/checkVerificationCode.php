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

  if($data['userPhone'] == "" || $data['verificationCode'] == ""){
      $message=["message"=>"fail","errorCode"=>112];
      echo json_encode($message);
      exit();
  }
  $userId=$data['userId'];
  $userPhone=$data['userPhone'];
  $userPhone=str_replace("-", " ", $userPhone);
  $verificationCode=$data['verificationCode'];

  $stmt = $pdo->prepare("SELECT * From `textTable` WHERE `userPhone` = '$userPhone' AND `textType` = '0';");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $code=$row['code'];
      $uploadTime=$row['uploadTime'];
    }
  }
  if($code == $verificationCode){
    $stmt = $pdo->prepare("UPDATE `userTable` SET `userPhone` = '$userPhone'
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
  echo json_encode(["message"=>"wrong code"]);
  exit();  
}
