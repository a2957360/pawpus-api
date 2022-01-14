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
    $userId=$_GET['userId'];
    //获取基本信息
    $serverInfo = array();
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["serverPets"] = json_decode($row["serverPets"], true);
        $serverInfo = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    $userId = $serverInfo['userId'];

    $petInfo = array();
    $stmt = $pdo->prepare("SELECT * From `petTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $petInfo[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    $serviceInfo = array();
    $stmt = $pdo->prepare("SELECT * From `serviceTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $serviceInfo[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$row]);
    exit();

  }
