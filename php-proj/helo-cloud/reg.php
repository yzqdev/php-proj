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
  <input maxlength="10" type="text" name="user_my" class="form-control" placeholder="Username" aria-describedby="sizing-addon2">
<input type="text" name="name_my" class="form-control" maxlength="10" placeholder="昵称" aria-describedby="sizing-addon2">
<input type="number" name="qq" class="form-control" maxlength="15" placeholder="QQ" aria-describedby="sizing-addon2">
<input type="password" name="pass" class="form-control" maxlength="20" placeholder="password" aria-describedby="sizing-addon2">
<br><p>验证码:<img id="IMG" src="captcha.php"></p><a href="javascript:void(0)" onclick="document.getElementById('IMG').src='captcha.php'">换一个</a>
<br><div class="input-group">
<div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">验证码</span>
  </div>
  <input type="text" name="code" class="form-control" placeholder="captcha-code" aria-describedby="sizing-addon2">
</div><br>
<input class="btn btn-primary"type="submit" name="submit" value="注册！"/><br>
<a class="btn btn-link" href="./login.php">已有账号？登录！</a>
  </form>
  </div>
</div>
</div>
</center>

</body>
</html>

<?php
$user_name = $_POST["user_my"];
$pass_word = $_POST["pass"];
$pass_first = $_POST["pass"];
$name_my = $_POST["name_my"];
$qq = $_POST["qq"];
if ($_POST["submit"]) {
if ($user_name == "") {
    echo '<script language="JavaScript">
swal({
title: "输入账号",
text: "只有您输入正确输入账号后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';
}else if ($pass_word == "") {
  echo '<script language="JavaScript">
swal({
title: "输入密码",
text: "只有您输入正确输入密码后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';
}else if ($name_my == "") {
  echo '<script language="JavaScript">
swal({
title: "输入昵称",
text: "只有您输入正确输入昵称后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';
}else if ($qq == "") {
  echo '<script language="JavaScript">
swal({
title: "输入QQ",
text: "只有您输入正确输入QQ后才可以发布文章",
icon: "error",
button: "确定",
});
</script>';
}
else if(isset($_REQUEST['code']))
  {
      session_start();
      if(strtolower($_REQUEST['code'])==$_SESSION['code']){
        if (file_exists("./data/".$user_name) == 1){
          echo '<script language="JavaScript">
          swal({
          title: "用户已经存在！",
          text: "验证码正确，但用户已经存在！",
          icon: "success",
          button: "确定",
          });
          </script>';
        }

        if (file_exists("./data/".$user_name) == 0) {
          //密码保存目录[使用时请更改]
          $reg_glod = "2";
          //注册赠送金币数量
          $sf_user = "u";
          $userr = "./data/".$user_name;
          $pass_path = "./pass/".$user_name;
          $pass_path1 = "./pass/";
          mkdir($userr,0777,true);
          mkdir($pass_path,0777,true);
          //创建用户目录
          $mainkey = $pass_path."/mainkey.txt";
      
          //=======================
          $pass_jiami = md5($pass_first); //实现密码md5加密
          file_put_contents($mainkey,$pass_jiami); //写入密码到密码目录
          //=======================
      
          $qqh=$userr."/qq.txt";
          file_put_contents($qqh,$qq);
          $name_file=$userr."/name.txt";
          file_put_contents($name_file,$user_name);
          $icon="http://q.qlogo.cn/headimg_dl?bs=qq&dst_uin=".$qq."&src_uin=www.qqjike.com&fid=blog&spec=640";
          $tx_file=$userr."/icon.txt";
          file_put_contents($tx_file,$icon);
          $glod=$userr."/glod.txt";
          file_put_contents($glod,$reg_glod);
          $sf=$userr."/sf.txt";
          file_put_contents($sf,$sf_user);
      
      //==============================
      //复制许可证到用户目录，这个是有道理的！
          $user_upload = "./upload/".$user_name;
          mkdir($user_upload,0777,true);
          //copy("./config/license.txt","./upload/".$user_name."/license.txt");
      //==============================
      echo '<script language="JavaScript">
      swal({
        title: "注册成功",
        text: "您的账号已经创建成功！",
        icon: "success",
        button: "确定",
      })
      .then((willDelete) => {
          location.href="./login.php";
        }
      );
      </script>';
        }
      }else{
        echo '<script language="JavaScript">
swal({
title: "验证码错误",
text: "验证码错误！",
icon: "error",
button: "确定",
});
</script>';
      }
      }

  }