<!doctype html>
<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>何乐云盘</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">

<script src="../js/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://strapdownjs.com/v/0.2/strapdown.js"></script>
<script src="../js/bootstrap.min.js"></script>


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
<div class="card" style="width: 60rem;">
<div class="card-body">
<div>
  <h3>HeloCloud Install File</h3>
  <div class="alert alert-warning" role="alert">
  安装本程序即表示同意readme中所列出的协议，请知悉！<br>
  IF YOU SUCCESSFULLY INSTALL HELOCLOUD YOU MUST KNOW IT!
</div>
  <div class="jumbotron jumbotron-fluid">
  <div class="container">
  <form method="post">
  <div class="form-group">
    <label for="exampleInputEmail1">QQ邮箱</label>
    <input type="email" name="user" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
  </div>
  <div class="form-group">
    <label for="exampleInputEmail1">管理员用户名</label>
    <input type="text" name="user_name" class="form-control">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">管理员密码</label>
    <input type="password" name="pass" class="form-control" id="exampleInputPassword1">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">程序标题</label>
    <input type="password" name="title" class="form-control" id="exampleInputPassword1">
  </div>
  </div>
</div>
  <p class="lead">
  <a class="btn btn-primary btn-lg" href="./install.php" role="button">上一步</a>
  <input class="btn btn-primary btn-lg" type="submit" name="submit" value="下一步"/><br>
  </p>
<br>
</form>
</div>
</div>
</div>
</div>

</center>



<?php
include '../footer.php';
include '../safety.php';

$install_file = "../install.ok";
if(is_file($install_file))
{
header('Location: ../index.php');
exit;
}else{
}
//----------------------
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
?>
</body>
</html>

<?php
$pass_first = $_POST["pass"];
$user = $_POST["user"];
$qq = $_POST["user"];
$user_name = $_POST["user_name"];
$pass = $_POST["pass"];
$title = $_POST["title"];
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
if ($_POST["submit"]) {
    if ($qq == "") {
        echo '<script language="JavaScript">
    swal({
    title: "输入账号",
    text: "只有您输入正确输入账号后才可以安装",
    icon: "error",
    button: "确定",
    });
    </script>';
    }else if ($user_name == "") {
      echo '<script language="JavaScript">
    swal({
    title: "输入昵称",
    text: "只有您输入正确输入昵称后才可以安装",
    icon: "error",
    button: "确定",
    });
    </script>';
    }else if ($pass == "") {
      echo '<script language="JavaScript">
    swal({
    title: "输入密码",
    text: "只有您输入正确输入密码后才可以安装",
    icon: "error",
    button: "确定",
    });
    </script>';
    }else if ($title == "") {
      echo '<script language="JavaScript">
    swal({
    title: "输入标题",
    text: "只有您输入正确输入标题后才可以安装",
    icon: "error",
    button: "确定",
    });
    </script>';
    }else{
    
    //----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
    $userr = "../data/".$user_name;
    $pass_path = "../pass/".$user_name;
    $pass_path1 = "../pass/";
    $reg_glod = "2";
    $sf_user = "a";//============管理员================
   //----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------

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
    $icon="http://q.qlogo.cn/headimg_dl?bs=qq&dst_uin=".$user."&spec=640";
    $tx_file=$userr."/icon.txt";
    file_put_contents($tx_file,$icon);
    $glod=$userr."/glod.txt";
    file_put_contents($glod,$reg_glod);
    $sf=$userr."/sf.txt";
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
    file_put_contents($sf,$sf_user);
    $install_file = "../config/install.ok";
    $install_ok_neirong = "ok";
    file_put_contents($install_file,$install_ok_neirong);
    $title_file = "../config/title.txt";
    file_put_contents($title_file,$title);
    $user_upload = "../upload/".$user_name;
    mkdir($user_upload,0777,true);
    echo '<script language="JavaScript">
      swal({
        title: "安装成功",
        text: "快来使用你的HeloCloud吧！",
        icon: "success",
        button: "确定",
      })
      .then((willDelete) => {
          location.href="../login.php";
        }
      );
      </script>';
    }
}
?>