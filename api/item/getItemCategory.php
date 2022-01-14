<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $categoryType=$_GET['categoryType'];
      $parentCategoryId=$_GET['parentCategoryId'];

      $list = array();
      $stmt = $pdo->prepare("SELECT * From `categoryTable` WHERE `categoryType`='2' AND `parentCategoryId`='0' order By `categoryOrder` DESC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["categoryName"] = json_decode($row["categoryName"], true); 
          $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
}

