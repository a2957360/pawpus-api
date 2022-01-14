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
    //获取当前语言
    $language=isset($data['language'])?$data['language']:$_POST['language'];

    //查询
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      // $categoryType=$data['categoryType'];
      $postId=$data['postId'];
      // $searchSql .= isset($categoryType)?" AND `categoryType`='$categoryType'":"";
      $searchSql .= isset($postId)?" AND `postId`='$postId'":"";
      $list = array();
      $stmt = $pdo->prepare("SELECT * From `postTable` WHERE 1 ".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["postTitle"] = json_decode($row["postTitle"], true); 
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
    if(isset($data['isDelete']) && isset($data['postId'])){
      $postId=$data['postId'];
      foreach ($postId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `postTable` WHERE `postId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    //修改排序
    if(isset($data['isChangeOrder']) && $data['isChangeOrder'] !== ""){
      //要更改的分类
      $postId = $data['postId'];
      $postOrder = $data['postOrder'];
      if($data['movement'] == "up"){
        $sql="(select min(`postOrder`) from `postTable` where `postOrder` > '$postOrder')";
      }else if($data['movement'] == "down"){
        $sql="(select max(`postOrder`) from `postTable` where `postOrder` < '$postOrder')";
      }
      $stmt = $pdo->prepare("SELECT * From `postTable` WHERE `postOrder` = ".$sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $postIdTwo = $row['postId'];
          $postOrderTwo = $row['postOrder'];
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
      if($categoryIdTwo == "0"){
        exit();
      }
      $stmt = $pdo->prepare("UPDATE `postTable` SET `postOrder` = '$postOrder' WHERE `postId` = '$postIdTwo'");
      $stmt->execute();
      if($stmt != null){
        $stmt = $pdo->prepare("UPDATE `postTable` SET `postOrder` = '$postOrderTwo' WHERE `postId` = '$postId'");
        $stmt->execute();
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加/修改
    $date = date('YmdHis');

    $postId=$data['postId'];
    $postImage=$data['postImage'];
    $postTitle=json_encode($data['postTitle'],JSON_UNESCAPED_UNICODE);
    $postContent=$data['postContent'];

    if(isset($postId) && $postId !== ""){
      $stmt = $pdo->prepare("UPDATE `postTable` SET `postImage` = '$postImage' ,`postTitle` = '$postTitle' ,`postContent` = '$postContent' WHERE `postId` = '$postId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `postTable`(`postImage`,`postTitle`,`postContent`) VALUES ('$postImage','$postTitle','$postContent')");
    $stmt->execute();
    if($stmt != null){
      $postId = $pdo->lastInsertId();
      $stmt = $pdo->prepare("UPDATE `postTable` SET `postOrder` = '$postId' WHERE `postId` = '$postId'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
    }
}

