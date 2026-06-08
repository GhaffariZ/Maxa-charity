<?php

$name = trim($_POST['component_name']);
$tag  = $_POST['component_tag'];
$code = $_POST['component_code'];

$dir = __DIR__."/components/".$name;

if(!is_dir($dir)){
mkdir($dir,0777,true);
}

if(!is_dir($dir."/images")){
mkdir($dir."/images",0777,true);
}

file_put_contents($dir."/component.php",$code);

file_put_contents(
$dir."/meta.json",
json_encode([
"tag"=>$tag
],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
);


/* ذخیره تصاویر */

if(isset($_FILES['component_images'])){

$total = count($_FILES['component_images']['name']);

for($i=0;$i<$total;$i++){

$tmp = $_FILES['component_images']['tmp_name'][$i];

if(!$tmp) continue;

$target = $dir."/images/".($i+1).".png";

move_uploaded_file($tmp,$target);

}

}

header("Location: component-create.php");
exit;
