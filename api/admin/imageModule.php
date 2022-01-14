<?php
  include("../../include/sql.php");
  include("arrayImages.php");
  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //获取图片
    if(isset($data['isGet']) && $data['isGet'] !== ""){
      $imageList = array();
      $stmt = $pdo->prepare("SELECT * From `imageTable` ORDER BY `createTime` DESC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['uid'] = $row['imageId'];
          $row['name'] = $row['imageName'];
          $row['status'] = "done";
          if(preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $row['imageurl'])){
            $row['display'] = $row['imageurl'];
            $row['url'] = $row['imageurl'];
          }else{
            $row['display'] = 'https://'.$_SERVER['SERVER_NAME']."/include/image/video.png";
            $row['url'] = $row['imageurl'];
          }
          $imageList[] = $row;
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }

      echo json_encode(["message"=>"success","data"=>$imageList]);
      exit();
    }

    //上传多个/单个图片
    if(isset($_POST['isUploadImage'])){
      $date=date("ymdhis");
      $returnImages = array();
      $uploadImages = reArrayFiles($_FILES['uploadImages']);
      foreach ($uploadImages as $imagekey => $imagevalue) {
        if($imagevalue['name'] != null){
          $File_type = strrchr($imagevalue['name'], '.'); 
          $dir = '../../include/pic/backend/';
          $pageImage = $dir.$date.$imagekey.rand(0,9).$File_type;
          if(!is_dir($dir)){
            mkdir($dir);
          }
          //上传图片
          move_uploaded_file($imagevalue['tmp_name'], $pageImage);
          // $picsql .= ",`uploadImages`='".$pageImage."'";
          $pageImage = str_replace("../", "", $pageImage);
          $returnImages[] = $imageurl = 'http://'.$_SERVER['SERVER_NAME']."/".$pageImage;
          $imageName = $imagevalue['name'];
          $stmt = $pdo->prepare("INSERT INTO `imageTable`(`imageurl`,`imageName`) VALUES ('$imageurl','$imageName')");
          $stmt->execute();
        }
      }
      echo json_encode(["message"=>"success","data"=>$returnImages]);
      exit();
    }

    if(isset($data['isDelete']) && $data['isDelete'] !== ""){
      $imageId = $data['imageId'];
      $stmt = $pdo->prepare("SELECT * From `imageTable` WHERE `imageId` = '$imageId'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $tmplink = "../".str_replace('https://'.$_SERVER['SERVER_NAME']."/","",$row['imageurl']);
            $stmt = $pdo->prepare("DELETE From `imageTable` WHERE `imageId` = '$imageId'");
            $stmt->execute();
            // echo  $tmplink;
            unlink($tmplink);
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      echo json_encode(["message"=>"delete success"]);
      exit();
    }

  }
