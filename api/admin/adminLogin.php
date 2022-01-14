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

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      $adminId=$data['adminId'];

      $adminList = array();
      $stmt = $pdo->prepare("SELECT * From `adminTable` WHERE `adminId`='$adminId'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $adminList[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$adminList]);
      exit();
    }

    //查询
    if(isset($data['isCheckPassword']) && $data['isCheckPassword'] !== ""){
      $adminName=$data['adminName'];
      $adminPassword=$data['adminPassword'];

      $adminList = array();
      $stmt = $pdo->prepare("SELECT * From `adminTable` WHERE `adminName`='$adminName'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          if(isset($row['adminPassword']) && password_verify($adminPassword,$row['adminPassword'])){
            echo json_encode(["message"=>"success","data"=>$row]);
            exit();
          }else{
            echo json_encode(["message"=>"error"]);
            exit();
          }
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"nouser"]);
      exit();
    }
    
    // $adminId=$data['adminId'];//就是adminId
    // $adminName=$data['adminName'];
    // $adminPassword=password_hash(isset($data['adminPassword'])?$data['adminPassword']:"", PASSWORD_DEFAULT);
    // //修改密码
    // if(isset($data['isChangePassword']) && $data['isChangePassword'] == "1"){
    //   // $stmt = $pdo->prepare("UPDATE `adminTable` SET `adminName` = '$adminName',`adminPassword` = '$adminPassword'
    //   //                       WHERE `adminId` = '$adminId'");
    //   $stmt = $pdo->prepare("UPDATE `adminTable` SET `adminPassword` = '$adminPassword'
    //                         WHERE `adminId` = '$adminId'");
    //   $stmt->execute();
    //   if($stmt != null){
    //     echo json_encode(["message"=>"success"]);
    //   }
    //   exit();
    // }


    // //修改
    // $stmt = $pdo->prepare("INSERT INTO `adminTable`(`adminName`,`adminPassword`) 
    //                         VALUES ('$adminName','$adminPassword')");
    // $stmt->execute();
    // if($stmt != null){
    //   echo json_encode(["message"=>"success"]);
    // }
    // exit();
  }
