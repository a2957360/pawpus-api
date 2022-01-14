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
    $stmt = $pdo->prepare("SELECT * From `userTable` WHERE `userId`='$userId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        unset($row['userPassword']);
        // unset($row['userState']);
        echo json_encode(["message"=>"success","data"=>$row]);
        exit();
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    echo json_encode(["message"=>"nouser"]);
    exit();

  }
