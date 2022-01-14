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

  $itemId=$_GET['itemId'];
  $saveuserId=$_GET['userId'];

  $categoryList = array();
  $stmt = $pdo->prepare("SELECT `categoryId`,`categoryName` From `categoryTable`");
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

  //获取规格
  $optionlist = array();
  $stmt = $pdo->prepare("SELECT * From `itemOptionTable` WHERE `itemId`='$itemId' AND `itemOptionState` = '0'");
  $stmt->execute();
  if($stmt != null){
  while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
    $optionlist[] = $row;
  }
  }else{
    echo json_encode(["message"=>"database error"]);
    exit();
  }

  $list = array();
  $stmt = $pdo->prepare("SELECT `itemTable`.*,`categoryTable`.`categoryName`,`savedTable`.`savedId` AS `isSaved` From `itemTable`
               LEFT JOIN `categoryTable` ON `categoryTable`.`categoryId` = `itemTable`.`itemCategory`
               LEFT JOIN `savedTable` ON `savedTable`.`targetId` = `itemTable`.`itemId` AND `savedTable`.`userId` = '$saveuserId' AND `savedTable`.`targetType` = '1'
               WHERE `itemTable`.`itemId`='$itemId'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["categoryName"] = json_decode($row["categoryName"], true); 
      $row["itemImage"] = json_decode($row["itemImage"], true);
      $row["itemCategory"] = json_decode($row["itemCategory"], true);
      $row["itemSubCategory"] = json_decode($row["itemSubCategory"], true);
      $row["itemPetCategory"] = json_decode($row["itemPetCategory"], true);
      $row["itemPetSubCategory"] = json_decode($row["itemPetSubCategory"], true);
      $row["isShowText"] = $row["itemState"] == 0 ?"上架":"下架";
      $row["isSaved"] = isset($row["isSaved"])?1:0;
      //获取category的名字
      $tmpSubCategory = array();
      foreach ($row["itemSubCategory"] as $key => $value) {
        $tmpSubCategory[] = isset($categoryList[$value])?$categoryList[$value]:"not exist";
      }
      $row["itemSubCategory"] = $tmpSubCategory;
      $tmpSubCategory=[];
      foreach ($row["itemPetCategory"] as $key => $value) {
        $tmpSubCategory[] = isset($categoryList[$value])?$categoryList[$value]:"not exist";
      }
      $row["itemPetCategory"] = $tmpSubCategory;
      $tmpSubCategory=[];
      foreach ($row["itemPetSubCategory"] as $key => $value) {
        $tmpSubCategory[] = isset($categoryList[$value])?$categoryList[$value]:"not exist";
      }
      $row["itemPetSubCategory"] = $tmpSubCategory;

      $row["itemOption"] = $optionlist;
      $list = $row;
      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }



  echo json_encode(["message"=>"no item"]);
  exit();

}
