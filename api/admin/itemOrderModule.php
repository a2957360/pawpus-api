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

  if(isset($data['isGet']) && $data['isGet'] !== ""){
    $itemOrderId=$data['itemOrderId'];
    $userId=$data['userId'];
    //  0:未支付;1:待发货;2:已发货;3:已收货;4:退款中;5:退款成功;6:拒绝退款
    $orderState =$data['orderState'];

    $itemOrderState=ITEM_ORDER_STATE; 

    $searchSql .= isset($serviceOrderId)?" AND `itemOrderTable`.`itemOrderId`='$itemOrderId'":"";
    $searchSql .= isset($userId)?" AND `itemOrderTable`.`userId`='$userId'":"";
    $searchSql .= isset($serverId)?" AND `itemOrderTable`.`orderState`='$orderState'":"";

    $list = array();
    $stmt = $pdo->prepare("SELECT `itemOrderTable`.*,`userTable`.`userName`
                           FROM `itemOrderTable` 
                           LEFT JOIN `userTable` ON `userTable`.`userId` = `itemOrderTable`.`userId`
                           WHERE `itemOrderTable`.`orderState`!='0' ".$searchSql." ORDER BY `itemOrderTable`.`createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["itemList"] = json_decode($row["itemList"], true); 
        $row["address"] = json_decode($row["address"], true); 
        $row["orderStateNo"] = $row["orderState"];
        $row["orderState"] = $itemOrderState[$row["orderState"]];
        $list[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$list]);
    exit();
  }

  if(isset($data['isGetStatistic']) && $data['isGetStatistic'] !== ""){
    $startDate=$data['startDate'];
    $endDate=$data['endDate'];

    $searchSql .= isset($startDate) && isset($endDate)?" AND `itemOrderTable`.`createTime`>='$startDate' AND `itemOrderTable`.`createTime`<='$endDate'":"";

    $list = array();
    $itemList = array();
    $orderNumber = 0;
    $income = 0;
    $stmt = $pdo->prepare("SELECT `itemOrderTable`.*,`userTable`.`userName`
                           FROM `itemOrderTable` 
                           LEFT JOIN `userTable` ON `userTable`.`userId` = `itemOrderTable`.`userId`
                           WHERE `itemOrderTable`.`orderState`>'0' AND `itemOrderTable`.`orderState`<'4' ".$searchSql."ORDER BY `createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){

        $income += (int)$row['total'];
        $orderNumber += 1;
        $row["itemList"] = json_decode($row["itemList"], true); 
        if(count($list) < 5){
          $list[] = $row;
        }
        foreach ($row["itemList"] as $key => $value) {
          $itemId = $value['itemId'];
          $itemQuantity = $value['itemQuantity'];
          $itemList[$itemId] = isset($itemList[$itemId])?(int)$itemList[$itemId]['itemQuantity'] + (int)$itemQuantity:(int)$itemQuantity;
        }
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }
    //排序-》取前十位-》取key-》转换成string
    arsort($itemList);
    $itemList =array_slice($itemList,0,10,true);
    $itemKeyList = array_keys($itemList);
    $itemString = implode(",", $itemKeyList);
    $itemReturn = array();
  	$stmt = $pdo->prepare("SELECT `itemTitle`,`itemImage`,`itemId` From `itemTable` WHERE `itemId` IN ($itemString) ORDER BY FIELD(`itemId`, $itemString)");
  	$stmt->execute();
  	if($stmt != null){
  		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
  			$row["itemImage"] = json_decode($row["itemImage"], true);
  			$row["itemQuantity"] = $itemList[$row['itemId']];
  			$itemReturn[] = $row;
  		}
  	}else{
  		echo json_encode(["message"=>"database error"]);
  		exit();
  	}

    $data = ["orderNumber"=>$orderNumber,"income"=>$income,"list"=>$list,"itemList"=>$itemReturn];

    echo json_encode(["message"=>"success","data"=>$data]);
    exit();
  }

  if(isset($data['isGetSummary']) && $data['isGetSummary'] !== ""){
    // $itemOrderId=$data['itemOrderId'];
    // $userId=$data['userId'];
    // //  0:未支付;1:待发货;2:已发货;3:已收货;4:退款中;5:退款成功;6:拒绝退款
    // $orderSate =$data['orderSate '];

    $itemOrderState=ITEM_ORDER_STATE; 

    // $searchSql .= isset($serviceOrderId)?" AND `itemOrderTable`.`itemOrderId`='$itemOrderId'":"";
    // $searchSql .= isset($userId)?" AND `itemOrderTable`.`userId`='$userId'":"";
    // $searchSql .= isset($serverId)?" AND `itemOrderTable`.`orderState`='$orderState'":"";
    $total = 0;
    $list = array();
    $stmt = $pdo->prepare("SELECT `itemOrderTable`.`total`,`itemOrderTable`.`createTime`,`userTable`.`userName`,'0' AS `type`
                           FROM `itemOrderTable` 
                           LEFT JOIN `userTable` ON `userTable`.`userId` = `itemOrderTable`.`userId`
                           WHERE `itemOrderTable`.`orderState`!='0' 
                           UNION ALL
                           SELECT `exchangePrice`,`createTime`,'提现','1' AS `type` FROM `exchangeTable`
                           WHERE `userId` = '0'
                           ORDER BY `createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        // $row["itemList"] = json_decode($row["itemList"], true); 
        // $row["address"] = json_decode($row["address"], true); 
        // $row["orderState"] = $itemOrderState[$row["orderState"]];
        $total = $row['type'] == 0?(int)$total + (int)$row['total'] :(int)$total - (int)$row['total'];
        $list[] = $row;
      }
    }else{ 
        echo json_encode(["message"=>"database error"]);  
        exit();
    }
    $data = ['list'=>$list,'total'=>$total];  
    echo json_encode(["message"=>"success111","data"=>$data]);
    exit();
  }

  if(isset($data['changeOrderState']) && $data['changeOrderState'] !== ""){
    $itemOrderId=$data['itemOrderId'];
    //  0:未支付;1:待发货;2:已发货;3:已收货;4:退款中;5:退款成功;6:拒绝退款
    $orderState =$data['orderState'];

    $itemOrderState=ITEM_ORDER_STATE; 


    $list = array();
    $stmt = $pdo->prepare("UPDATE `itemOrderTable` SET `orderState` = '$orderState'
                           WHERE `itemOrderId`='$itemOrderId'");
    $stmt->execute();
    if($stmt != null){
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }
    echo json_encode(["message"=>"UPDATE `itemOrderTable` SET `orderState` = '$orderState'
                           WHERE `itemOrderId`='$itemOrderId'"]);
    exit();
  }

}