<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $momentId = $_GET['momentId'];
  $loginUserId = $_GET['loginUserId'];
  $offset = isset($_GET['offset'])?$_GET['offset']:0;
  $limit = 3;


  $momentInfo = array();
  //savedTable查询是否当前登录用户已经收藏
  $stmt = $pdo->prepare("SELECT `momentTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`userTable`.`userDescription`,`savedTable`.`savedId`,`savedNumberTable`.`momentSaveNumber`
                         From `momentTable`
                         LEFT JOIN `userTable` ON `momentTable`.`userId` = `userTable`.`userId`
                         LEFT JOIN `savedTable` ON `savedTable`.`userId` = '$loginUserId'
                         AND `savedTable`.`targetId` = `momentTable`.`momentId` AND `savedTable`.`targetType` = '2'
                         LEFT JOIN (SELECT count(`savedId`) AS `momentSaveNumber`,`targetId` FROM `savedTable` WHERE `targetType` = '2' AND `targetId` = '$momentId' GROUP BY `targetId`) 
                         `savedNumberTable`
                          ON `savedNumberTable`.`targetId` = `momentTable`.`momentId`
                         WHERE `momentId`='$momentId' AND `momentType` = '0'");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["momentImage"] = json_decode($row["momentImage"], true); 
      //判断是否like
      $row["momentLike"] = json_decode($row["momentLike"], true); 
      $row["momentLikeNumber"] = count($row["momentLike"]);
      $key = array_search((string)$loginUserId,$row["momentLike"]);
      $row["isLiked"] = false; 
      if($key !== false){
        $row["isLiked"] = true; 
      }
      unset($row["momentLike"]);

      //判断是否收藏
      $row["isSaved"] = false; 
      if(isset($row["savedId"])){
        $row["isSaved"] = true; 
      }
      
      $momentInfo = $row;
    }
  }else{
    echo json_encode(["message"=>"database error"]);  
    exit();
  }

  $rootlist = array();
  // $subquery = array();
  $stmt = $pdo->prepare("SELECT `replyTable`.*,`userTable`.`userName`,`userTable`.`userImage`,count(`sub`.`replyId`) AS `subNumber` From `replyTable`
                         LEFT JOIN `userTable` ON `replyTable`.`userId` = `userTable`.`userId`
                         LEFT JOIN `replyTable` `sub` ON `replyTable`.`replyId` = `sub`.`replyToReplyId`
                         WHERE `replyTable`.`momentId`='$momentId' AND `replyTable`.`replyToReplyId` = '0' AND `replyTable`.`atUserId` = '0' 
                         GROUP BY `replyTable`.`replyId`
                         ORDER BY `replyTable`.`createTime`");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $rootlist[] = $row;
      // $subquery[] = $row['replyId'];
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  // $subquery = implode(",", $subquery);
  // $stmt = $pdo->prepare("SELECT `replyTable`.*,`userTable`.`userName` From `replyTable`
  //                        LEFT JOIN `userTable` ON `replyTable`.`userId` = `userTable`.`userId`
  //                        LEFT JOIN `replyTable` `countTable` ON `replyTable`.`replyToReplyId` = `countTable`.`replyToReplyId` AND `replyTable`.`createTime` < `countTable`.`createTime`
  //                        WHERE `replyTable`.`momentId`='$momentId' AND `replyTable`.`replyToReplyId` IN ($subquery) 
  //                        GROUP BY `replyTable`.`replyToReplyId`,`replyTable`.`replyId`
  //                        HAVING count(`countTable`.`replyId`) < $limit
  //                        ORDER BY `replyTable`.`createTime`");
  // $stmt->execute();
  // if($stmt != null){
  //   while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
  //     $row['atUserName'] = $rootlist[$row['replyToReplyId']]['userName'];
  //     $rootlist[$row['replyToReplyId']]['subReplyList'][] = $row;
  //   }
  // }else{
  //     echo json_encode(["message"=>"database error"]);  
  //     exit();
  // }

  $rootlist = array_values($rootlist);
  $momentInfo['replyList'] = $rootlist;
  echo json_encode(["message"=>"success","data"=>$momentInfo]);
  exit();
}
