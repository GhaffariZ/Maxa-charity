<?php
session_start();

if(isset($_SESSION["user_id"])){
header("Location: /dashboard/dashboard.php");
exit;
}

session_start();
require_once "../config/db.php";

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if(empty($email) || empty($password)){
        $error = "لطفا ایمیل و رمز عبور را وارد کنید";
    }else{

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s",$email);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 1){

            $user = $result->fetch_assoc();

            if(password_verify($password,$user["password"])){

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: /dashboard/dashboard.php");
                exit;

            }else{
                $error = "رمز عبور اشتباه است";
            }

        }else{
            $error = "کاربری با این ایمیل یافت نشد";
        }

    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>

<meta charset="UTF-8">
<title>ورود</title>

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

.login-box{
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
background:#4CAF50;
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

<div class="login-box">

<h2>ورود به حساب</h2>

<form method="post">

<input type="email" name="email" placeholder="ایمیل">

<input type="password" name="password" placeholder="رمز عبور">

<button type="submit">ورود</button>

</form>

<?php
if($error){
echo "<div class='error'>$error</div>";
}
?>

<a href="registry.php">ثبت نام</a>

</div>

</body>
</html>
