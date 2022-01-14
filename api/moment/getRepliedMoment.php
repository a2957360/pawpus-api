<?php
  include("../../include/sql.php");
  include("../../include/conf/config.php");

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $loginUserId = $_GET['loginUserId'];
  $offset = isset($_GET['offset'])?$_GET['offset']:0;
  $limit = QUERY_LIMIT;


  $list = array();
  $stmt = $pdo->prepare("SELECT `momentId`,`momentImage`,`momentTitle`,`momentLike`,`userTable`.`userName`,`userTable`.`userImage` From `replyTable`
                         LEFT JOIN `momentTable` ON `momentTable`.`momentId` = `replyTable`.`momentId`
                         LEFT JOIN `userTable` ON `momentTable`.`userId` = `userTable`.`userId`
                         WHERE `momentType` = '0' AND `replyTable`.`userId` = '$loginUserId' AND `momentTable`.`userId` != '$loginUserId'
                         GROUP BY `replyTable`.`momentId`
                         limit $offset, $limit");
  $stmt->execute();
  if($stmt != null){
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      $row["momentImage"] = json_decode($row["momentImage"], true); 
      $row["momentLike"] = json_decode($row["momentLike"], true); 
      $row["momentLikeNumber"] = count($row["momentLike"]);
      $key = array_search((string)$loginUserId,$row["momentLike"]);
      $row["isLiked"] = false; 
      if($key != false){
        $row["isLiked"] = true; 
      }
      unset($row["momentLike"]);
      $list[] = $row;
    }
  }else{
      echo json_encode(["message"=>"database error"]);
      exit();
  }

  echo json_encode(["message"=>"success","data"=>$list,"offset"=>$offset + count($list)]);
  exit();
}
