<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  require_once "../sendemail.php";

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //网站url
    $websiteLink = WEBSITE_LINK;
    $newEmail=$data['newEmail'];
    $userId=$data['userId'];
    //判断用户信息
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $userName = $row['userName'];
      if(!isset($userName)){
        echo json_encode(["message"=>"no user","errorCode"=>105]);
        exit();
      }
      if($row['userEmail'] == $newEmail){
        echo json_encode(["message"=>"same email","errorCode"=>110]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error","errorCode"=>111]);
        exit();
    }

    //判断新邮箱是否存在
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userEmail`='$newEmail'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      if(isset($row['userId'])){
        echo json_encode(["message"=>"exist email","errorCode"=>109]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error","errorCode"=>111]);
        exit();
    }

    try {
      //0:登陆确认;1:忘记密码确认;2:修改邮箱;
      $emailPurpose = 2;
      $str = md5(uniqid(md5(microtime(true)),true));
      $emailToken = sha1($str.$userId);

      $stmt = $pdo->prepare("UPDATE `emailTable` SET 
                        `emailToken` = '$emailToken',`newEmail` = '$newEmail'
                        WHERE `userId` = '$userId' AND `emailPurpose` = '$emailPurpose'");
      $stmt->execute();
      if($stmt->rowCount() == 0){
        $stmt = $pdo->prepare("INSERT INTO `emailTable`(`userId`,`emailToken`,`newEmail`,`emailPurpose`) 
                                VALUES ('$userId','$emailToken','$newEmail','$emailPurpose')");
        $stmt->execute();
        $emailId = $pdo->lastInsertId();
      }else{
        $stmt = $pdo->prepare("SELECT * From `emailTable` WHERE `userId` = '$userId' AND `emailPurpose` = '$emailPurpose'");
        $stmt->execute();
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        $emailId = $row['emailId'];
      }

      if($stmt->rowCount() > 0 || $emailId != 0){
        $mail->addAddress($newEmail);     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML
        // $mail->AddEmbeddedImage('static/img/icon.png','logo');
        $mail->Subject = 'Change Email PawPus';
        // $mail->AddEmbeddedImage('include/image/1597326264.jpg','bg');
        $link = $websiteLink."activate-account?token=".$emailToken."&id=".$emailId;
        $mail->Body = "Change Email?".PHPEOL." Please click link to confirm <a href='".$link."'>Change Email<a/>";
        if($mail->send()){
          echo json_encode(["message"=>"success"]);
          exit();
        }
      }else{
        echo json_encode(["message"=>"database error","errorCode"=>111]);
        exit();
      }

    }catch (Exception $e) {
      echo json_encode(["message"=>"email send error","errorCode"=>102]);
      exit(); 
    }

  }
