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

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $languageList = LANGUAGE_LIST;
    //获取当前语言
    $language=isset($data['language'])?$data['language']:$_POST['language'];

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      $categoryType=$data['categoryType'];
      $categoryId=$data['categoryId'];
      $searchSql .= isset($categoryType)?" AND `categoryType`='$categoryType'":"";
      $searchSql .= isset($categoryId)?" AND `categoryId`='$categoryId'":"";

      $list = array();
      $stmt = $pdo->prepare("SELECT `categoryTable`.*,`parent`.`categoryName` AS `parentName` From `categoryTable` 
                              LEFT JOIN `categoryTable` `parent` ON `parent`.`categoryId` = `categoryTable`.`parentCategoryId`
                              WHERE 1 ".$searchSql."order By `categoryOrder` DESC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["categoryName"] = json_decode($row["categoryName"], true); 
          $row["parentName"] = json_decode($row["parentName"], true); 
          $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    //删除
    if(isset($data['isDelete']) && isset($data['categoryId'])){
      $categoryId=$data['categoryId'];
      foreach ($categoryId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `categoryTable` WHERE `categoryId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //修改排序
    if(isset($data['isChangeOrder']) && $data['isChangeOrder'] !== ""){
      //要更改的分类
      $categoryId = $data['categoryId'];
      $categoryOrder = $data['categoryOrder'];
      $categoryType = $data['categoryType'];
      $categoryLevel = $data['categoryLevel']==0?" AND `parentCategoryId` = 0":" AND `parentCategoryId` != 0";
      if($data['movement'] == "up"){
        $sql="(select min(`categoryOrder`) from `categoryTable` where `categoryOrder` > '$categoryOrder' AND `categoryType`='$categoryType'".$categoryLevel.")";
      }else if($data['movement'] == "down"){
        $sql="(select max(`categoryOrder`) from `categoryTable` where `categoryOrder` < '$categoryOrder' AND `categoryType`='$categoryType'".$categoryLevel.")";
      }
      $stmt = $pdo->prepare("SELECT * From `categoryTable` WHERE `categoryOrder` = ".$sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $categoryIdTwo = $row['categoryId'];
          $categoryOrderTwo = $row['categoryOrder'];
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
      if(!isset($categoryIdTwo) || $categoryIdTwo == "0"){
        exit();
      }
      $stmt = $pdo->prepare("UPDATE `categoryTable` SET `categoryOrder` = '$categoryOrder' WHERE `categoryId` = '$categoryIdTwo'");
      $stmt->execute();
      if($stmt != null){
        $stmt = $pdo->prepare("UPDATE `categoryTable` SET `categoryOrder` = '$categoryOrderTwo' WHERE `categoryId` = '$categoryId'");
        $stmt->execute();
        echo json_encode(["message"=>"SELECT * From `categoryTable` WHERE `categoryOrder` = ".$sql]);
      }
      exit();
    }

    //添加/修改
    $date = date('YmdHis');

    $categoryId=$data['categoryId'];
    $parentCategoryId=$data['parentCategoryId'];
    $categoryName = json_encode($data['categoryName'],JSON_UNESCAPED_UNICODE);
    $categoryImage=$data['categoryImage'];
    $categoryType=$data['categoryType'];

    if(isset($categoryId) && $categoryId !== ""){
      $stmt = $pdo->prepare("UPDATE `categoryTable` SET `parentCategoryId` = '$parentCategoryId' ,`categoryName` = '$categoryName',`categoryImage` = '$categoryImage' WHERE `categoryId` = '$categoryId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `categoryTable`(`parentCategoryId`,`categoryName`,`categoryImage`,`categoryType`) VALUES ('$parentCategoryId','$categoryName','$categoryImage','$categoryType')");
    $stmt->execute();
    if($stmt != null){
      $categoryId = $pdo->lastInsertId();
      $stmt = $pdo->prepare("UPDATE `categoryTable` SET `categoryOrder` = '$categoryId' WHERE `categoryId` = '$categoryId'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
    }
}

