<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $userId=$_GET['userId'];

  $list = array();
  $stmt = $pdo->prepare("SELECT * From `petTable`
                         WHERE `userId`= '$userId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["petType"] = json_decode($row["petType"], true); 
      $row["petImage"] = json_decode($row["petImage"], true); 
      $row["petPortfolio"] = json_decode($row["petPortfolio"], true); 
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }
  echo json_encode(["message"=>"success","data"=>$list]);
  exit();

}
