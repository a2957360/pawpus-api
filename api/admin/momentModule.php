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
    $userId = $data['userId'];

    $searchSql .= isset($userId)?" AND `momentTable`.`userId`='$userId'":"";

    $list = array();
    $stmt = $pdo->prepare("SELECT `momentTable`.*,`userTable`.`userName`,`userTable`.`userImage` From `momentTable`
                           LEFT JOIN `userTable` ON `momentTable`.`userId` = `userTable`.`userId`
                           WHERE `momentType` = '0' ".$searchSql);
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["momentImage"] = json_decode($row["momentImage"], true); 
        $row["momentLikeNumber"] = count($row["momentLike"]);
        unset($row["momentLike"]);
        $list[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$list]);
    exit();
  }

  if(isset($data['isGetSingle']) && $data['isGetSingle'] !== ""){
    $momentId = $data['momentId'];

    // $searchSql .= isset($momentId)?" AND `momentTable`.`momentId`='$momentId'":"";

    $list = array();
    $stmt = $pdo->prepare("SELECT `momentTable`.*,`userTable`.`userName`,`userTable`.`userImage` From `momentTable`
                           LEFT JOIN `userTable` ON `momentTable`.`userId` = `userTable`.`userId`
                           WHERE `momentType` = '0' AND `momentTable`.`momentId`='$momentId'".$searchSql);
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["momentImage"] = json_decode($row["momentImage"], true); 
        $row["momentLikeNumber"] = count($row["momentLike"]);
        unset($row["momentLike"]);
        $list = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    $replylist = array();
    $stmt = $pdo->prepare("SELECT `replyTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`atUser`.`userName` AS `atUserName`,`atUser`.`userImage` AS `atUserImage` From `replyTable`
                           LEFT JOIN `userTable` ON `replyTable`.`userId` = `userTable`.`userId`
                           LEFT JOIN `userTable` `atUser` ON `replyTable`.`atUserId` = `atUser`.`userId`
                           WHERE `momentId` = '$momentId'");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $replylist[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);  
        exit();
    }
    $list['replylist'] = $replylist;
    echo json_encode(["message"=>"success","data"=>$list]);
    exit();
  }

  if(isset($data['isGetReport']) && $data['isGetReport'] !== ""){
    $list = array('moment'=>[],'reply'=>[]);
    $stmt = $pdo->prepare("SELECT `momentTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`reportTable`.`reportContent`,
                          `reporter`.`userName` AS `reportName`,`reporter`.`userImage` AS `reportImage` From `reportTable`
                           LEFT JOIN `momentTable` ON `momentTable`.`momentId` = `reportTable`.`targetId`
                           LEFT JOIN `userTable` ON `momentTable`.`userId` = `userTable`.`userId`
                           LEFT JOIN `userTable` `reporter` ON `reportTable`.`userId` = `reporter`.`userId`
                           WHERE `reportTable`.`targetType`='0' AND `momentType` = '0' ORDER BY `reportTable`.`createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["momentImage"] = json_decode($row["momentImage"], true);
        $list['moment'][] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    $stmt = $pdo->prepare("SELECT `replyTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`reportTable`.`reportContent`,
                          `reporter`.`userName` AS `reportName`,`reporter`.`userImage` AS `reportImage` From `reportTable`
                           LEFT JOIN `replyTable` ON `replyTable`.`replyId` = `reportTable`.`targetId`
                           LEFT JOIN `userTable` ON `replyTable`.`userId` = `userTable`.`userId`
                           LEFT JOIN `userTable` `reporter` ON `reportTable`.`userId` = `reporter`.`userId`
                           WHERE  `reportTable`.`targetType`='1' ORDER BY `reportTable`.`createTime` DESC");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["momentImage"] = json_decode($row["momentImage"], true);
        $list['reply'][] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$list]);
    exit();
  }

  //删除
  if(isset($data['isDelete']) && (isset($data['momentId']) || isset($data['replyId']))){
    $momentId=$data['momentId'];
    if(isset($momentId)){
      $stmt = $pdo->prepare("DELETE FROM `momentTable` WHERE `momentId` = '$momentId'");
      $stmt->execute();
      $stmt = $pdo->prepare("DELETE FROM `replyTable` WHERE `momentId` = '$momentId'");
      $stmt->execute();
      $stmt = $pdo->prepare("DELETE FROM `reportTable` WHERE `targetId` = '$momentId' AND `targetType` = '0'");
      $stmt->execute();
    }
    $replyId=$data['replyId'];
    if(isset($replyId)){
      $stmt = $pdo->prepare("DELETE FROM `replyTable` WHERE `replyId` = '$replyId'");
      $stmt->execute();
      $stmt = $pdo->prepare("DELETE FROM `reportTable` WHERE `targetId` = '$replyId' AND `targetType` = '1'");
      $stmt->execute();
    }
    echo json_encode(["message"=>"success"]);
    exit();
  }

}
