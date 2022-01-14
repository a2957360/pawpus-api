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
      $id=$data['id'];
      // $itemCategory=$data['itemCategory'];
      // $itemParentCategory=$data['itemParentCategory'];
      // $searchText=$data['searchText'];

      $searchSql .= isset($id)?"WHERE `id`=".$id:"";
      // $searchSql .= isset($itemCategory)?" AND FIND_IN_SET('".$itemCategory."',`itemTable`.`itemCategory`)":"";
      // $searchSql .= isset($itemParentCategory)?" AND FIND_IN_SET('".$itemParentCategory."',`itemTable`.`itemParentCategory`)":"";
      // $searchSql .= isset($searchText)?"AND (`itemTag` LIKE '%".$searchText."%' OR `itemTitle` LIKE '%".$searchText."%')":"";

      $list = array();
      $stmt = $pdo->prepare("SELECT * From `homePage`".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['isShowText'] = $row['isShow']==1?"Showing":"Hiding";
          $row["title"] = json_decode($row["title"], true);
          $row["subTitle"] = json_decode($row["subTitle"], true);
          $row["topTitle"] = json_decode($row["topTitle"], true);
          $row["image"] = json_decode($row["image"], true);
          $row["button"] = json_decode($row["button"], true);
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
    if(isset($data['isDelete']) && isset($data['id'])){
      $id=$data['id'];
      foreach ($id as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `homePage` WHERE `id` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"DELETE FROM `homePage` WHERE `id` = '$value'"]);
      exit();
    }

    //上架下架
    if(isset($data['isChangeState']) && isset($data['isChangeState'])){
      $id=$data['id'];
      $isShow=$data['isShow'];
      $stmt = $pdo->prepare("UPDATE `homePage` SET `isShow` = '$isShow' WHERE `id` = '$id'");
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

    $id=$data['id'];
    $title=json_encode($data['title'],JSON_UNESCAPED_UNICODE);
    $subTitle=json_encode($data['subTitle'],JSON_UNESCAPED_UNICODE);
    $topTitle=json_encode($data['topTitle'],JSON_UNESCAPED_UNICODE);   
    $image=json_encode($data['image'],JSON_UNESCAPED_UNICODE);  
    $button=json_encode($data['button'],JSON_UNESCAPED_UNICODE);
    $buttonlink=$data['buttonlink'];

    if(isset($id) && $id !== ""){

      $stmt = $pdo->prepare("UPDATE `homePage` SET `title` = '$title' ,`subTitle` = '$subTitle' , `topTitle` = '$topTitle' , `image` = '$image' ,
                            `button` = '$button' , `buttonlink` = '$buttonlink' WHERE `id` = '$id'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
      }
      exit();
    }

    //添加
    $stmt = $pdo->prepare("INSERT INTO `homePage`(`title`,`subTitle`,`topTitle`,`image`,`button`,`buttonlink`)
                          VALUES ('$title','$subTitle','$topTitle','$image','$button','$buttonlink')");
    $stmt->execute();
    if($stmt != null){
      echo json_encode(["message"=>"success"]);
    }

  }
