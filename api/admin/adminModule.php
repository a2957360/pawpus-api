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
      $storeList = array();
      $stmt = $pdo->prepare("SELECT * From `adminTable`");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          // $row["storePayment"] = json_decode($row["storePayment"], true);
          $row["adminTypeDisplay"] = $row["adminType"] == 0 ?"商城管理员":"全站管理员";
          $storeList[] = $row;
        } 
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$storeList]);
      exit();
    }

    

    //删除
    if(isset($data['isDelete']) && isset($data['adminId'])){
      $adminId=$data['adminId'];
      foreach ($adminId as $key => $value) {
        $data = $value;
        $stmt = $pdo->prepare("DELETE FROM `adminTable`WHERE `adminId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //添加/修改 
    $adminId=$data['adminId'];
    $adminName=$data['adminName'];
    $adminPassword=isset($data['adminPassword'])?password_hash($data['adminPassword'], PASSWORD_DEFAULT):null;
    $adminType=$data['adminType'];

    //修改
    if(isset($adminId) && $adminId !== ""){
      $stmt = $pdo->prepare("UPDATE `adminTable` SET  
                            `adminName` = '$adminName' , `adminPassword` = '$adminPassword'
                            WHERE `adminId` = '$adminId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }
    
    //没有填写信息不添加
    if(!isset($adminName) || !isset($adminName)){
      exit();
    }

    $stmt = $pdo->prepare("INSERT INTO `adminTable`(`adminName`, `adminPassword`, `adminType`)
                           VALUES ('$adminName','$adminPassword','$adminType')");
    $stmt->execute();
    if($stmt != null){
        $adminId = $pdo->lastInsertId();
        if($adminId != 0){
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
