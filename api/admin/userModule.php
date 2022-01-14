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
    $userState = USER_STATE;

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){

      $list = array();
      $stmt = $pdo->prepare("SELECT `userTable`.*, sum(`serviceOrderTable`.`serviceOrderTotalPrice`) AS `serviceTotal`,
                             sum(`itemOrderTable`.`total`) AS `itemTotal` From `userTable`
                            LEFT JOIN `serviceOrderTable` ON `serviceOrderTable`.`userId` = `userTable`.`userId` AND `serviceOrderTable`.`orderState` > 0
                            LEFT JOIN `itemOrderTable` ON `itemOrderTable`.`userId` = `userTable`.`userId` AND `itemOrderTable`.`orderState` > 0
                            GROUP BY `userTable`.`userId`".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['userStateText']=$userState[$row['userState']];
          $row['serviceTotal']=round($row['serviceTotal'],2);
          $row['itemTotal']=round($row['itemTotal'],2);
          $row['userBlockText']=$row['userBlock'] == 0?"正常":"黑名单";
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
    if(isset($data['isDelete']) && isset($data['userId'])){
      $userId=$data['userId'];
      foreach ($userId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `userTable` WHERE `userId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //删除
    if(isset($data['isChangeState']) && isset($data['userId'])){
      $userId=$data['userId'];
      $userBlock=$data['userBlock'];
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userBlock` = '$userBlock'WHERE `userId` = '$userId'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //添加/修改
    $date = date('YmdHis');

    $userId=$data['userId'];
    $icon=$data['icon'];
    $link=$data['link'];

    if(isset($userId) && $userId !== ""){
      $stmt = $pdo->prepare("UPDATE `userTable` SET `parentCategoryId` = '$parentCategoryId' ,`categoryName` = '$categoryName',`categoryType` = '$categoryType' WHERE `userId` = '$userId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

}

