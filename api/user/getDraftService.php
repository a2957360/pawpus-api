<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $userId=$_GET['userId'];
  $searchSql .= isset($userId)?" AND `serviceTable`.`userId`='$userId'":"";


  //删除了`serviceTable`.`serviceState` = '-1' 
  $list = array();
  $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName` From `serviceTable` 
                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          WHERE 1 ".$searchSql." Group BY `serviceTable`.`serviceId`");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["serviceImage"] = json_decode($row["serviceImage"], true);
      $row["serviceSubCategory"] = json_decode($row["serviceSubCategory"], true);
      $row["serviceExtra"] = json_decode($row["serviceExtra"], true);
      $row["serviceFacility"] = json_decode($row["serviceFacility"], true);
      $row["serviceBlockDate"] = json_decode($row["serviceBlockDate"], true);
      $row["servicePet"] = json_decode($row["servicePet"], true);
      $row["serviceLanguage"] = json_decode($row["serviceLanguage"], true);

      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list]);
  exit();
}
