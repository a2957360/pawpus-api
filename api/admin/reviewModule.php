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

	  $targetId=$data['targetId'];
    $targetType=$data['targetType'];
	  $searchSql .= isset($targetId)?" AND `targetId`=".$targetId:"";
    $searchSql .= isset($targetType)?" AND `targetType`=".$targetType:"";
    

	  $list = array();
	  $stmt = $pdo->prepare("SELECT `reviewTable`.*,`userTable`.`userName`,
                            `storeUser`.`userName` AS `storeName`,`storeUser`.`userImage` AS `storeImage` From `reviewTable`
	                         LEFT JOIN `userTable` ON `userTable`.`userId` = `reviewTable`.`fromId`
                           LEFT JOIN `serviceTable` ON `serviceTable`.`serviceId` = `reviewTable`.`targetId`
                           LEFT JOIN `userTable` `storeUser` ON `storeUser`.`userId` = `serviceTable`.`userId`
	                         WHERE 1 ".$searchSql);
	  $stmt->execute();
	  if($stmt != null){
	    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
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
    if(isset($data['isDelete']) && isset($data['reviewId'])){
      $reviewId=$data['reviewId'];
      foreach ($reviewId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `reviewTable` WHERE `reviewId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }
}
