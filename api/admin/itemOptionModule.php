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
      $searchSql .= isset($itemId)?" AND `itemId`='$itemId'":"";
      
  		$list = array();
  		$stmt = $pdo->prepare("SELECT * From `itemOptionTable` WHERE 1 ".$searchSql);
  		$stmt->execute();
  		if($stmt != null){
  		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["itemOptionStateText"] = $row["itemOptionState"] == 0 ?"显示":"隐藏";
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
    if(isset($data['isDelete']) && isset($data['itemOptionId'])){
      $itemOptionId=$data['itemOptionId'];
      foreach ($itemOptionId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `itemOptionTable` WHERE `itemOptionId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //审核/黑名单/恢复
    if(isset($data['isChangeState']) && isset($data['isChangeState'])){
      $itemOptionId=$data['itemOptionId'];
      $itemOptionState =$data['itemOptionState']; 
      $stmt = $pdo->prepare("UPDATE `itemOptionTable` SET `itemOptionState ` = '$itemOptionState ' WHERE `itemOptionId` = '$itemOptionId'");
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

    $itemOptionId=$data['itemOptionId'];
    $itemId=$data['itemId'];
    $itemOptionName=$data['itemOptionName'];
    $itemOptionPrice=$data['itemOptionPrice'];
    $itemOptionSalePrice=$data['itemOptionSalePrice'];
    $itemOptionSku=$data['itemOptionSku'];
    $itemOptionStock=$data['itemOptionStock'];
    $itemOptionState =$data['itemOptionState '];

    if(isset($itemOptionId) && $itemOptionId !== ""){
      $stmt = $pdo->prepare("UPDATE `itemOptionTable` SET `itemOptionName` = '$itemOptionName' ,`itemOptionPrice` = '$itemOptionPrice' ,
                            `itemOptionSalePrice` = '$itemOptionSalePrice', `itemOptionSku` = '$itemOptionSku', `itemOptionStock` = '$itemOptionStock', `itemOptionState` = '$itemOptionState'
                            WHERE `itemOptionId` = '$itemOptionId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `itemOptionTable`(`itemId`, `itemOptionName`, `itemOptionPrice`, `itemOptionSalePrice`, `itemOptionSku`,`itemOptionStock`)
                           VALUES ('$itemId','$itemOptionName','$itemOptionPrice','$itemOptionSalePrice','$itemOptionSku','$itemOptionStock')");
    $stmt->execute();
    if($stmt != null){
        $serviceId = $pdo->lastInsertId();
        if($serviceId != 0){
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"success"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
}

