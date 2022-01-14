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

    $userEmail=$data['userEmail'];
    $userPassword=$data['userPassword'];
    $reUserPassword=$data['reUserPassword'];
    if($userPassword != $reUserPassword){
      echo json_encode(["message"=>"password not match","errorCode"=>101]);
      exit();
    }
    $userPassword=password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO `userTable`(`userEmail`,`userPassword`) VALUES ('$userEmail','$userPassword')");
    $stmt->execute();
    if($stmt != null){
        $userId = $pdo->lastInsertId();
        if($userId != 0){
          try {
            //0:登陆确认;1:忘记密码确认;2:修改邮箱;
            $emailPurpose = 0;
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
              $mail->Subject = 'PawPus';
              // $mail->AddEmbeddedImage('include/image/1597326264.jpg','bg');
              // $link = $websiteLink."user/emailValidation.php?token=".$emailToken."&id=".$emailId;
              $link = $websiteLink."activate-account?token=".$emailToken."&id=".$emailId;
              $mail->Body = "Thank you regist on PawPus Please click link to active account <a href='".$link."'>Active Account<a/>";
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
          echo json_encode(["message"=>"signup failed","errorCode"=>111]);
          exit(); 
        }
    }else{
      echo json_encode(["message"=>"database error","errorCode"=>111]);
      exit();
    }
  }
