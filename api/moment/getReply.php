<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $replyToReplyId = $_GET['replyToReplyId'];
  $momentId = $_GET['momentId'];
  $offset = isset($_GET['offset'])?$_GET['offset']:0;
  $limit = QUERY_LIMIT;

  if(!isset($replyToReplyId) && !isset($momentId)){
    echo json_encode(["message"=>"input error"]);  
    exit();
  }
  
  $searchSql .= isset($replyToReplyId)?" AND `replyToReplyId`=".$replyToReplyId:"";
  $searchSql .= isset($momentId)?" AND `momentId`=".$momentId." AND `replyType` = '0'":"";

  $rootlist = array();
  $stmt = $pdo->prepare("SELECT `replyTable`.*,`userTable`.`userName`,`userTable`.`userImage`,`atUser`.`userName` AS `atUserName` From `replyTable`
                         LEFT JOIN `userTable` ON `replyTable`.`userId` = `userTable`.`userId`
                         LEFT JOIN `userTable` `atUser` ON `replyTable`.`atUserId` = `atUser`.`userId`
                         WHERE 1 ".$searchSql." limit $offset, $limit");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);  
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list,"offset"=>$offset + count($list)]);
  exit();
}
