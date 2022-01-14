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

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
  		//服务状态中文
  		$serviceState = SERVICE_STATE;

      $itemId=$data['itemId'];
      $searchSql .= isset($itemId)?" AND `itemId`=".$itemId:"";
      
  		$list = array();
  		$stmt = $pdo->prepare("SELECT * From `itemTable` WHERE 1 ".$searchSql);
  		$stmt->execute();
  		if($stmt != null){
  		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
  		  $row["itemImage"] = json_decode($row["itemImage"], true);
  		  // $row["itemCategory"] = json_decode($row["itemCategory"], true);
        $row["itemSubCategory"] = json_decode($row["itemSubCategory"], true);
        $row["itemPetCategory"] = json_decode($row["itemPetCategory"], true);
        $row["itemPetSubCategory"] = json_decode($row["itemPetSubCategory"], true);
        $row["itemOption"] = json_decode($row["itemOption"], true);
        $row["isShowText"] = $row["itemState"] == 0 ?"上架":"下架";
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
    if(isset($data['isDelete']) && isset($data['itemId'])){
      $itemId=$data['itemId'];
      foreach ($itemId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `itemTable` WHERE `itemId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"DELETE FROM `itemTable` WHERE `itemId` = '$value'"]);
      exit();
    }

    //审核/黑名单/恢复
    if(isset($data['isChangeState']) && isset($data['isChangeState'])){
      $itemId=$data['itemId'];
      $itemState =$data['itemState']; 
      $stmt = $pdo->prepare("UPDATE `itemTable` SET `itemState` = '$itemState' WHERE `itemId` = '$itemId'");
      $stmt->execute();
      if($stmt != null){

      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }


    //添加/修改
    $date = date('YmdHis');

    $itemId=$data['itemId'];
    $itemTitle=$data['itemTitle'];
    $itemImage = json_encode($data['itemImage'],JSON_UNESCAPED_UNICODE);
    $itemDescription = $data['itemDescription'];
    $itemShortDescription = $data['itemShortDescription'];
    $itemPrice=$data['itemPrice'];
    $itemSalePrice=$data['itemSalePrice'];
    $itemTax=$data['itemTax'];
    // $itemCategory = json_encode($data['itemCategory'],JSON_UNESCAPED_UNICODE);
    $itemCategory = $data['itemCategory'];
    $itemSubCategory = json_encode($data['itemSubCategory'],JSON_UNESCAPED_UNICODE);
    $itemPetCategory = json_encode($data['itemPetCategory'],JSON_UNESCAPED_UNICODE);
    $itemPetSubCategory = json_encode($data['itemPetSubCategory'],JSON_UNESCAPED_UNICODE);
    // $itemOption = json_encode($data['itemOption'],JSON_UNESCAPED_UNICODE);

    if(isset($itemId) && $itemId !== ""){
      $stmt = $pdo->prepare("UPDATE `itemTable` SET `itemTitle` = '$itemTitle' ,`itemImage` = '$itemImage' ,
                            `itemDescription` = '$itemDescription',`itemShortDescription` = '$itemShortDescription',
                             `itemTax` = '$itemTax', 
                            `itemCategory` = '$itemCategory', `itemSubCategory` = '$itemSubCategory',`itemPetCategory` = '$itemPetCategory',`itemPetSubCategory` = '$itemPetSubCategory'
                            WHERE `itemId` = '$itemId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"UPDATE `itemTable` SET `itemTitle` = '$itemTitle' ,`itemImage` = '$itemImage' ,
                            `itemDescription` = '$itemDescription',`itemShortDescription` = '$itemShortDescription',
                             `itemTax` = '$itemTax', 
                            `itemCategory` = '$itemCategory', `itemSubCategory` = '$itemSubCategory',`itemPetCategory` = '$itemPetCategory',`itemPetSubCategory` = '$itemPetSubCategory'
                            WHERE `itemId` = '$itemId'"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `itemTable`(`itemId`, `itemTitle`, `itemImage`, `itemDescription`, `itemShortDescription`, `itemTax`, `itemCategory`,
                             `itemSubCategory`,`itemPetCategory`,`itemPetSubCategory`)
                           VALUES ('$itemId','$itemTitle','$itemImage','$itemDescription','$itemShortDescription','$itemTax','$itemCategory','$itemSubCategory',
                           '$itemPetCategory','$itemPetSubCategory')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
        if($serviceId != 0){
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
}

