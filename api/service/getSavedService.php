<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $serviceState = SERVICE_STATE;
  $limit = QUERY_LIMIT;
  $offset=isset($_GET['offset'])?$_GET['offset']:0;

  $userId=$_GET['userId'];
  // $targetId=$_GET['targetId'];

  $list = array();
  $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`,`userTable`.`userName`,`userTable`.`userImage`,`userTable`.`serverStar`,`userTable`.`serverLevel`  From `savedTable` 
                          LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `savedTable`.`targetId`
                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
                          WHERE `savedTable`.`userId` = '$userId' AND `savedTable`.`targetType` = '0'
                          Group BY `serviceTable`.`serviceId`
                          limit $offset, $limit");
  $stmt->execute();
  // $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`  From `savedTable` 
  //                         LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `savedTable`.`targetId`
  //                         LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
  //                         LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
  //                         WHERE `savedTable`.`userId` = '$userId' AND `savedTable`.`targetId` = '$targetId' AND `savedTable`.`targetType` = '0'
  //                         Group BY `serviceTable`.`serviceId`
  //                         limit $offset, $limit");
  // $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true);
      $row["serviceSubCategory"] = json_decode($row["serviceSubCategory"], true);
      $row["serviceExtra"] = json_decode($row["serviceExtra"], true);
      $row["serviceImage"] = json_decode($row["serviceImage"], true);
      $row["serviceStateDisplay"] = $serviceState[$row["serviceState"]];
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list,"offset"=>$offset + count($list)]);
  exit();
}
