<?php
session_start();
require_once "../config/db.php";

$error="";
$success="";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$name=trim($_POST["name"]);
$email=trim($_POST["email"]);
$password=$_POST["password"];

if(empty($name) || empty($email) || empty($password)){

$error="همه فیلدها الزامی است";

}else{

if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
$error="ایمیل معتبر نیست";
}else{

$sql="SELECT id FROM users WHERE email=?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows>0){

$error="این ایمیل قبلا ثبت شده";

}else{

$hash=password_hash($password,PASSWORD_DEFAULT);

$sql="INSERT INTO users (name,email,password,created_at)
VALUES (?,?,?,NOW())";

$stmt=$conn->prepare($sql);
$stmt->bind_param("sss",$name,$email,$hash);
$stmt->execute();

$user_id=$conn->insert_id;

$_SESSION["user_id"]=$user_id;
$_SESSION["user_name"]=$name;
$_SESSION["user_email"]=$email;

header("Location: /dashboard/dashboard.php");
exit;

}

}

}

}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>

<meta charset="UTF-8">
<title>ثبت نام</title>

<style>

*{box-sizing:border-box}
html,body{margin:0;padding:0}
body{
font-family:tahoma;
background:#f5f5f5;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.box{
background:white;
padding:30px;
width:320px;
border-radius:8px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

input{
width:100%;
padding:10px;
margin-top:10px;
border:1px solid #ccc;
border-radius:4px;
}

button{
margin-top:15px;
width:100%;
padding:10px;
background:#2196F3;
color:white;
border:none;
border-radius:4px;
}

.error{
color:red;
margin-top:10px;
}

a{
display:block;
margin-top:15px;
text-align:center;
}

</style>

</head>

<body>

<div class="box">

<h2>ثبت نام</h2>

<form method="post">

<input type="text" name="name" placeholder="نام">

<input type="email" name="email" placeholder="ایمیل">

<input type="password" name="password" placeholder="رمز عبور">

<button type="submit">ایجاد حساب</button>

</form>

<?php
if($error){
echo "<div class='error'>$error</div>";
}
?>

<a href="login.php">ورود</a>

</div>

</body>
</html>
