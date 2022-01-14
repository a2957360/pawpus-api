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

  $serviceId=$_GET['serviceId'];
  $saveuserId=$_GET['userId'];

  $categoryList = array();
  $stmt = $pdo->prepare("SELECT `categoryId`,`categoryName` From `categoryTable` 
                          WHERE (`parentCategoryId` != '0' AND `categoryType`= '0') OR `categoryType`= '1' OR `categoryType`= '5'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $categoryList[$row['categoryId']] = $row['categoryName'];
    }
  }else{
      echo json_encode(["message"=>"database error"]);
      exit();
  }

  $userId = 0;
  $list = array();
  $stmt = $pdo->prepare("SELECT `serviceTable`.*,`categoryTable`.`categoryName`,`userTable`.`userName`,`userTable`.`userImage`,`userTable`.`serverLevel`,`reviewTable`.`reviewNum`,
                          `savedTable`.`savedId` AS `isSaved`
                          From `serviceTable` 
                          LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `serviceTable`.`serviceCategory`
                          LEFT JOIN `userTable` ON `userTable`.`userId` = `serviceTable`.`userId`
                          LEFT JOIN (SELECT sum(`reviewId`) AS `reviewNum`,`targetId` FROM `reviewTable` WHERE `targetType` = '0' GROUP BY `targetId`) `reviewTable`
                          ON `reviewTable`.`targetId` = `serviceTable`.`serviceId`
                          LEFT JOIN `savedTable` ON `savedTable`.`targetId` = `serviceTable`.`serviceId` AND `savedTable`.`userId` = '$saveuserId' AND `savedTable`.`targetType` = '0'
                          WHERE `serviceTable`.`serviceState` = '1' AND `serviceBlock` = '0' AND `serviceTable`.`serviceId` = '$serviceId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["serviceSubCategory"] = json_decode($row["serviceSubCategory"], true);
      $row["serviceExtra"] = json_decode($row["serviceExtra"], true);
      $row["serviceFacility"] = json_decode($row["serviceFacility"], true);
      $row["serviceImage"] = json_decode($row["serviceImage"], true);
      $row["serviceStateDisplay"] = $serviceState[$row["serviceState"]];
      $row["serviceBlockDate"] = json_decode($row["serviceBlockDate"], true);
      $row["isSaved"] = isset($row["isSaved"])?1:0;

      //获取宠物信息
      $servicePet = json_decode($row["servicePet"], true);

      //获取category的名字
      $tmpSubCategory = array();
      foreach ($row["serviceSubCategory"] as $key => $value) {
        if(isset($categoryList[$key])){
          $tmpSubCategory[] = ["id"=>$key,"name"=>$categoryList[$key],"price"=>$value];
        }
      }
      $row["serviceSubCategory"] = $tmpSubCategory;
      //获取额外服务的名字
      $tmpExtra = array();
      foreach ($row["serviceExtra"] as $key => $value) {
        if(isset($categoryList[$key])){
          $tmpExtra[] = ["id"=>$key,"name"=>$categoryList[$key],"price"=>$value];
        }
      }
      $row["serviceExtra"] = $tmpExtra;
      //获取基础设施
      $tmpFacility = array();
      foreach ($row["serviceFacility"] as $key => $value) {
        $tmpFacility[] = isset($categoryList[$value])?$categoryList[$value]:"not exist";
      }
      $row["serviceFacility"] = $tmpFacility;
      $userId=$row['userId'];
      $list = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }
  //没有获取到service
  if(!isset($list['serviceId'])){
    echo json_encode(["message"=>"no service"]);
    exit();
  }

  $servicePet = implode(",", $servicePet);
  $petlist = array();
  $stmt = $pdo->prepare("SELECT `petType` From `petTable`
                         -- LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `petTable`.`petType`
                         WHERE `petId` IN ($servicePet)");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["petType"] = json_decode($row["petType"], true); 
      $petlist[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }
  $list['petList'] = $petlist;
  echo json_encode(["message"=>"success","data"=>$list]);
  exit();



}
