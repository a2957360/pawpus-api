<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");
  require_once "../sendemail.php";
  // include("../jwt.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail=$data['userEmail'];
    $userPassword=$data['userPassword'];
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userEmail`='$userEmail'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        //先验证邮箱和密码是否正确 => 再验证是否active
        if(isset($row['userPassword']) && password_verify($userPassword,$row['userPassword'])){
          if($row['userState'] == 0){
            echo json_encode(["message"=>"no active","errorCode"=>103]);
            exit();
          }
          unset($row['userPassword']);
          //生成token
          $payload_test=array('iss'=>'admin',
                    'iat'=>time(),
                    'exp'=>time()+7200,
                    // 'nbf'=>time(),
                    'userId'=>'1',
                    'sub'=>'www.admin.com',
                    'jti'=>md5(uniqid('JWT').time())
                    );
          $token_test=Jwt::getToken($payload_test);
          $row['token'] = $token_test;
          echo json_encode(["message"=>"success","data"=>$row]);
          exit();
        }else{
          echo json_encode(["message"=>"not match","errorCode"=>104]);
          exit();
        }
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    echo json_encode(["message"=>"nouser","errorCode"=>105]);
    exit();

  }
