<!DOCTYPE html>
<html lang="CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php 
$title=@file_get_contents("./config/title.txt");
echo $title.'- Powered by HeloCloud';
?></title>
<link rel="Shortcut Icon" href="./image/title.ico" type="image/x-icon" />
<link rel="stylesheet" href="./css/bootstrap.min.css">
<script src="./js/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="./js/bootstrap.min.js"></script>
</head>
<body>
<?php
//头部样式
include './header.php';
//----------------------
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
?>
<br>
<br>
<br>
<center>
<div class="container">
<div class="card" style="width: 20rem;">
<div class="card-body">
<blockquote class="blockquote">
  <p class="mb-0">用户登录</p>
  login and upload
</blockquote>
<form method="post"  autocomplete="off">
<div class="input-group">
<div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">用户名</span>
  </div>
  <input type="text" name="user" class="form-control" placeholder="Username" aria-describedby="sizing-addon2">
</div><br>
<div class="input-group">
<div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">密码</span>
  </div>
  <input type="password" name="pass" class="form-control" placeholder="password" aria-describedby="sizing-addon2">
</div><br>
   <p>验证码:<img id="IMG" src="captcha.php"></p><a href="javascript:void(0)" onclick="document.getElementById('IMG').src='captcha.php'">换一个</a>
<br><div class="input-group">
<div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">验证码</span>
  </div>
  <input type="text" name="code" class="form-control" placeholder="captcha-code" aria-describedby="sizing-addon2">
</div><br>
<input class="btn btn-primary" type="submit" name="submit" value="登录"/><br>
<a class="btn btn-link" href="./reg.php">没有账号？注册一个！</a>
  </form>
  </div>
</div>
</div>
</center>

</body>
</html>

<?php
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$user_name = $_POST["user"];
$pass = $_POST["pass"];
$pass_get = $_POST["pass"];
if ($_POST["submit"]) {
  if ($user_name == "") {
      echo '<script language="JavaScript">
swal({
title: "输入账号",
text: "只有您输入正确输入账号后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';//账号为空 
  } else if ($pass == "") {
      echo '<script language="JavaScript">
swal({
title: "密码为空",
text: "只有您输入正确输入密码后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';
      //密码为空
      //----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
  } else if(isset($_REQUEST['code']))
  {
      session_start();
      if(strtolower($_REQUEST['code'])==$_SESSION['code']){
        if (file_exists("./data/" .$user_name)) {

          include './config/p_config.php';
        $password=file_get_contents($pass_path."/mainkey.txt");
        //取得密码目录
            $pass_jiami = md5($pass_get);
            //加密密码
            if ($password == $pass_jiami) //密码比较
            {
                $time = 1 * 60 * 60 * 24 * 365;
                //setcookie(name,value,expire,path,domain,secure)
                //name	必需。规定 cookie 的名称。
                //value	必需。规定 cookie 的值。
                //expire	可选。规定 cookie 的有效期。
                //path	可选。规定 cookie 的服务器路径。
                //domain	可选。规定 cookie 的域名。
                //secure	可选。规定是否通过安全的 HTTPS 连接来传输 cookie。
                setcookie("key", $user, time() + $time);
                setcookie("password", $pass_jiami, time() + $time);
                //cookie保存md5加密后的密码
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
                echo '
      <script language="JavaScript">
      swal({
      title: "登录成功",
      text: "成功登录您的账号！",
      icon: "success",
      button: "确定",
      })
      .then((willDelete) => {
        location.href="./upload/";
      }
      );
      </script>';
            } else {
                echo '<script language="JavaScript">
      swal({
      title: "密码错误",
      text: "只有您输入正确密码后才可以登录",
      icon: "error",
      button: "确定",
      });
      </script>';
            }
      
        } else {
            echo '<script language="JavaScript">
      swal({
      title: "用户不存在",
      text: "用户不存在，请注册后使用",
      icon: "error",
      button: "确定",
      });
      </script>';
        }
      }else{
        echo '<script language="JavaScript">
        swal({
        title: "验证码错误",
        text: "只有您输入正确验证码后才可以登录",
        icon: "error",
        button: "确定",
        });
        </script>';
      }
      //----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
      }else{
        echo '<script language="JavaScript">
        swal({
        title: "验证码为空",
        text: "只有您输入正确验证码后才可以登录",
        icon: "error",
        button: "确定",
        });
        </script>';
      }

  }
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$user_if = $_COOKIE['key'];
if ($user_if == "") {
} else {
    echo '
  <script language="JavaScript">
  swal({
    title: "你已经登录",
    text: "你已经登录，无需二次登录！",
    icon: "error",
    button: "确定",
  })
  .then((willDelete) => {
      location.href="./index.php";
    }
  );
  </script>';
}