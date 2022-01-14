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
    $serverLevel = SERVER_LEVEL;

    //查询列表
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      $id=$data['id'];
      $searchSql .= isset($id)?" AND `id`=".$id:"";

      $userState=$data['userState'];
      //  0:未激活;1：普通用户;2:服务者信息草稿;3:服务者待审核;4:服务者;5:黑名单 
      if($userState == 3){
        $sql = "SELECT `userTable`.* From `userTable`
                LEFT JOIN `serviceTable` ON `userTable`.`userId` = `serviceTable`.`userId` AND `serviceTable`.`serviceState` = '0'
                WHERE `userState` = '$userState' OR `serviceTable`.`serviceId` IS NOT NULL
                GROUP BY `userTable`.`userId`";
      }else{
        $sql = "SELECT `userTable`.*,`serviceOrderTable`.`orderTotal` From `userTable`
                LEFT JOIN (SELECT SUM(`serviceOrderTotalPrice`) AS `orderTotal`,`serverId` FROM `serviceOrderTable` GROUP BY `serverId`) `serviceOrderTable` 
                ON `serviceOrderTable`.`serverId` = `userTable`.`userId`
                WHERE `userState` = '$userState'";
      }
      $list = array();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          // $row['isShowText'] = $row['isShow']==1?"Showing":"Hiding";
          $row['serverLevel'] = $serverLevel[$row['serverLevel']];
          $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    //查询单个
    if(isset($data['isGetSingle']) && $data['isGetSingle'] !== ""){
      $userId=$data['userId'];

      $list = array();
      $stmt = $pdo->prepare("SELECT * From `userTable` 
                             WHERE `userId` = '$userId'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $list = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    //查询日历
    if(isset($data['isGetDate']) && $data['isGetDate'] !== ""){
      $userId=$data['userId'];

      $list = array();
      $stmt = $pdo->prepare("SELECT `serviceOrderTable`.*,`userTable`.`userName` From `serviceOrderTable`
                            LEFT JOIN `userTable` ON `serviceOrderTable`.`userId` = `userTable`.`userId`
                            WHERE `userId` = '$userId'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["serviceOrderDays"] = json_decode($row["serviceOrderDays"], true);
          $row["serviceOrderPetInfo"] = json_decode($row["serviceOrderPetInfo"], true);
          foreach ($row["serviceOrderDays"] as $key => $value) {
            $list[$value][] = ['userName'=>$row["userName"],'pet'=>$row["serviceOrderPetInfo"]];
          }
          //获取serviceId查找block日期
          $serviceId = $row["serviceId"];
          // $list[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      $stmt = $pdo->prepare("SELECT `serviceBlockDate` From `serviceTable` WHERE `serviceId` = '$serviceId'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["serviceBlockDate"] = json_decode($row["serviceBlockDate"], true);
          foreach ($row["serviceBlockDate"] as $key => $value) {
            $list[$value][] = ['userName'=>"商家休息",'pet'=>[]];
          }
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"success","data"=>$list]);
      exit();
    }

    // //删除
    // if(isset($data['isDelete']) && isset($data['id'])){
    //   $id=$data['id'];
    //   foreach ($id as $key => $value) {
    //     $stmt = $pdo->prepare("DELETE FROM `homePage` WHERE `id` = '$value'");
    //     $stmt->execute();
    //   }
    //   echo json_encode(["message"=>"DELETE FROM `homePage` WHERE `id` = '$value'"]);
    //   exit();
    // }

    //审核/黑名单/恢复
    if(isset($data['isChangeState']) && isset($data['isChangeState'])){
      $userId=$data['userId'];
      $userState=$data['userState']; 
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userState` = '$userState' WHERE `userId` = '$userId'");
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

    $userId=$data['userId'];
    $userName=$data['userName'];
    $userPhone=$data['userPhone'];
    $userImage=$data['userImage'];
    $userState=$data['userState'];
    $serverLevel=$data['serverLevel'];

    if(isset($userId) && $userId !== ""){
      //有subtitle
      $stmt = $pdo->prepare("UPDATE `userTable` SET 
                            `userName` = '$userName',`userImage` = '$userImage',`userState` = '$userState',`serverLevel` = '$serverLevel',`userPhone` = '$userPhone'
                            WHERE `userId` = '$userId' AND `userState` != '0'");
      $stmt->execute();
      if($stmt->rowCount() > 0){
        echo json_encode(["message"=>"success"]);
        exit();
      }else{
        echo json_encode(["message"=>"change error"]);
        exit();  
      }
    }

    //添加
    // $stmt = $pdo->prepare("INSERT INTO `userTable`(`userName`,`userImage`,`userState`,`image`,`button`,`buttonlink`)
    //                       VALUES ('$userName','$userImage','$topTitle','$image','$button','$buttonlink')");
    // $stmt->execute();
    // if($stmt != null){
    //   echo json_encode(["message"=>"success"]);
    // }
}

