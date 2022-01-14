<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $token=$_GET['token'];
    $id=$_GET['id'];
    $stmt = $pdo->prepare("SELECT * From `emailTable` WHERE `emailId`='$id'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $emailToken=$row['emailToken'];
        //0:登陆确认;1:忘记密码确认;
        $emailPurpose=$row['emailPurpose'];
        $newEmail=$row['newEmail'];
        $userId=$row['userId'];
      }
    }else{
        echo json_encode(["message"=>"database error","errorCode"=>111]);
        exit();
    }
    //判断是否存在link
    if($emailPurpose == null){
      echo json_encode(["message"=>"link error","errorCode"=>107]);
      exit();
    }
    //更新用户状态
    if($emailPurpose == 0){
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userState` = '1' WHERE `userId` = '$userId' AND `userState` = '0'");
      $stmt->execute();
    }else if($emailPurpose == 2){
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userEmail` = '$newEmail' WHERE `userId` = '$userId' AND `userState` != '0'");
      $stmt->execute();
    }
    if($emailPurpose != 1){
      //删除email
      $stmt = $pdo->prepare("DELETE From `emailTable` WHERE `emailId`='$id'");
      $stmt->execute();   
    }

    //返回用户信息
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      unset($row['userPassword']);    
    }else{
        echo json_encode(["message"=>"database error","errorCode"=>111]);
        exit();
    }
    echo json_encode(["message"=>"success","data"=>$row]);
    exit();
  }
