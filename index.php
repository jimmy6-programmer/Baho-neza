<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
  header('location:dashboard.php');
}
$error_mssg = "";
$result = "";
if (isset($_POST['login'])) {
  $name = $_POST['username'];
  $pass = $_POST['password'];

  $name = mysqli_real_escape_string($db,$name);
  $pass = mysqli_real_escape_string($db,$pass);

  $sql = mysqli_query($db,"SELECT * FROM `admin` WHERE username='$name' AND password='$pass'");
  $result = mysqli_num_rows($sql);
  if ($result>0) {
    $data = mysqli_fetch_array($sql);
    $s_name = $data['username'];
    $_SESSION['username'] = $s_name;
    header('location:dashboard.php');
  }else{
    $error_mssg = "Oops!, Wrong username or password";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <title>Sign in Form</title>
  </head>
  <body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="#" method="POST" class="sign-in-form">
            <h2 class="title">Sign in</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="username" placeholder="Username" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" placeholder="Password" />
            </div>
            <input type="submit" name="login" value="Login" class="btn solid" />   
          </form>
          <p class="error-mssg"><?php echo $error_mssg; ?></p>
        </div>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>Welcome again admin!</h3>
            <p>
              Baho-neza food ltd system is here for you to simplify your work
            </p>
          </div>
          <img src="img/1-removebg-preview.png" class="image" alt="" />
        </div>
      </div>
    </div>
    
    <script src="app.js"></script>
  </body>
</html>
