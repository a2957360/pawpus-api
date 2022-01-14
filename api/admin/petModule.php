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
      $petId=$data['petId'];
      $userId=$data['userId'];
      $searchSql .= isset($serviceId)?" AND `petId`=".$petId:"";
      $searchSql .= isset($userId)?" AND `userId`=".$userId:"";

      $list = array();
      $stmt = $pdo->prepare("SELECT * From `petTable` WHERE 1 ".$searchSql);
      $stmt->execute();
      if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["petPortfolio"] = json_decode($row["petPortfolio"], true); 
        $row["petImage"] = json_decode($row["petImage"], true);
        $row["petGender"] = $row["petGender"]==0?"母":"公";
        $row["isOperated"] = $row["isOperated"]==0?"未绝育":"绝育";
        $list[] = $row;
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

    $petId=$data['petId'];
    $userId=$data['userId'];
    $petName=$data['petName'];
    $petImage = json_encode($data['petImage'],JSON_UNESCAPED_UNICODE);
    $petType=$data['petType'];
    $petVariety=$data['petVariety'];
    $petAge=$data['petAge'];
    $petPortfolio=$data['petPortfolio'];

    if(isset($serviceId) && $serviceId !== ""){
      //有subtitle
      // $stmt = $pdo->prepare("UPDATE `subPage` SET `title` = '$title' ,`subTitle` = '$subTitle' , `topTitle` = '$topTitle' , `image` = '$image' ,
      //                       `button` = '$button' , `buttonlink` = '$buttonlink', `leftImage` = '$leftImage', `rightImage` = '$rightImage', `content` = '$content', `map` = '$map', `address` = '$address' WHERE `id` = '$id'");
      $stmt = $pdo->prepare("UPDATE `petTable` SET `petName` = '$petName' , `petImage` = '$petImage' ,`petType` = '$petType' ,
                            `petVariety` = '$petVariety', `petAge` = '$petAge', `petPortfolio` = '$petPortfolio'
                            WHERE `serviceId` = '$serviceId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `petTable`(`userId`, `petName`, `petImage`, `petType`, `petVariety`,`petAge`, `petPortfolio`)
                           VALUES ('$userId','$petName','$petImage','$petType','$petVariety','$petAge','$petPortfolio')");
    $stmt->execute();
    if($stmt != null){
        $petId = $pdo->lastInsertId();
        if($petId != 0){
          echo json_encode(["message"=>"success"]);
          exit();
        }
        echo json_encode(["message"=>"fail"]);
        exit();
    }else{
      echo json_encode(["message"=>"database error"]);
      exit();
    }
}

