<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $targetId=$_GET['serviceId'];

  $list = array();
  $stmt = $pdo->prepare("SELECT * From `reviewTable` WHERE `targetId`= '$targetId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list]);
  exit();

}
