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

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //网站url
    $websiteLink = WEBSITE_LINK;
    
    $userEmail=$_GET['userEmail'];
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userEmail`='$userEmail'");
    $stmt->execute();
    if($stmt != null){
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $userId = $row['userId'];
      if(!isset($userId)){
        echo json_encode(["message"=>"no user","errorCode"=>105]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    if($userId != 0){
      try {
        //0:登陆确认;1:忘记密码确认;2:修改邮箱;
        $emailPurpose = 1;
        $str = md5(uniqid(md5(microtime(true)),true));
        $emailToken = sha1($str.$userId);
        //解决id自增问题
        $stmt = $pdo->prepare("UPDATE `emailTable` SET 
                          `emailToken` = '$emailToken'
                          WHERE `userId` = '$userId' AND `emailPurpose` = '$emailPurpose'");
        $stmt->execute();
        if($stmt->rowCount() == 0){
          $stmt = $pdo->prepare("INSERT INTO `emailTable`(`userId`,`emailToken`,`emailPurpose`) 
                                VALUES ('$userId','$emailToken','$emailPurpose')");
          $stmt->execute();
          $emailId = $pdo->lastInsertId();
        }else{
          $stmt = $pdo->prepare("SELECT * From `emailTable` WHERE `userId` = '$userId' AND `emailPurpose` = '$emailPurpose'");
          $stmt->execute();
          $row=$stmt->fetch(PDO::FETCH_ASSOC);
          $emailId = $row['emailId'];
        }

        if($stmt->rowCount() >0 || $emailId != 0){
          $mail->addAddress($userEmail);     // Add a recipient
          $mail->isHTML(true);                                  // Set email format to HTML
          // $mail->AddEmbeddedImage('static/img/icon.png','logo');
          $mail->Subject = 'Forget Password PawPus';
          // $mail->AddEmbeddedImage('include/image/1597326264.jpg','bg');
          // $link = $websiteLink."user/emailValidation.php?token=".$emailToken."&id=".$emailId;
          $link = $websiteLink."reset-password?token=".$emailToken."&id=".$emailId;
          $mail->Body = "Forget Password? Please click link to reset <a href='".$link."'>Active Account<a/>";
          if($mail->send()){
            echo json_encode(["message"=>"success"]);
            exit();
          }
        }
      }catch (Exception $e) {
        echo json_encode(["message"=>"email send error","errorCode"=>102]);
        exit(); 
      }
    }else{
      echo json_encode(["message"=>"no user","errorCode"=>105]);
      exit(); 
    }

  }
