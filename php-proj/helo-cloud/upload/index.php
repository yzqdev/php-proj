<!DOCTYPE html>
<html lang="CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php 
$title=@file_get_contents("../config/title.txt");
echo $title.'个人中心 - Powered by HeloCloud';
?></title>
<link rel="Shortcut Icon" href="../image/title.ico" type="image/x-icon" />
<link rel="stylesheet" href="../css/bootstrap.min.css">
<script src="../js/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://strapdownjs.com/v/0.2/strapdown.js"></script>
<script src="../js/bootstrap.min.js"></script>
</head>
</head>
<body>
<?php
//头部样式
include './header.php';
//----------------------
?>
<div class="container">
<br>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$icon=@file_get_contents("../data/".$user."/icon.txt");
if($user_if == "")
{
  echo '<br><br><center><img width="130px" height="130px" class="shadow p-3 mb-5 bg-white rounded" class="img-thumbnail" src="../image/1.png"></center>';
}else{
  echo '<br><br><center><img width="130px" height="130px" class="shadow p-3 mb-5 bg-white rounded" class="img-thumbnail" src='.$icon.'></center>';
}
?>
<div class="card">
<div class="title"><span class="badge badge-light">昵称</span>
<?php
$user=$_COOKIE['key'];
$nc=@file_get_contents("../data/".$user."/name.txt");
echo $nc;
?>

</div>
<div class="title"><span class="badge badge-light">QQ</span>
<?php
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$user=$_COOKIE['key'];
$qq=@file_get_contents("../data/".$user."/qq.txt");
echo $qq;
?></div>

<div class="title"><span class="badge badge-light">金币</span>
<?php
$user=$_COOKIE['key'];
$glod=@file_get_contents("../data/".$user."/glod.txt");
echo $glod;
?></div>

<div class="title"><span class="badge badge-light">存储空间</span>
<?php
$user=$_COOKIE['key'];
$chunchu =@file_get_contents("../data/".$user."/now_user_number.txt");
echo $chunchu."MB/10240MB(10GB)";
?></div>

<div class="title"><span class="badge badge-light">权限</span>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$sf = @file_get_contents("../data/".$user."/sf.txt");
if($sf=="u")
{
	echo '<span class="badge badge-dark">普通用户</span>';
} else {
	echo '';
}
if($sf=="a")
{
	echo '<span class="badge badge-warning">管理员<a href="../admin/">>>>进入管理后台<<<</a></span>';
} else {
	echo '<span class="badge badge-danger">非法权限组</span>';
}
?>
</div>
</div>
<br>

<div class="alert alert-dark" role="alert">
  请勿上传非法文件，如果发现，将进行封号并向有关部门进行举报核实！
</div>

<center>
<a class="btn btn-success" href="../notecloud" role="button">开始使用云笔记</a>
</center>
<br>
<div class="card">
  <div class="card-body">
    <h5 class="card-title">我们只允许上传的文件类型名称:</h5>
    <p class="card-text">apk,sb,sb2,sb3,cpp,asm,h,iso,img,reg,bat,docx,pptx,md,c,class,ttf,psd,dat,sh,log,flv,3gp,gif,jpeg,jpg,png,zip,rar,doc,txt,sql,js,7z,exe,mp3,mp4,wav,ppt,pdf,wps,ppt,dll,mdb,xml,xls,ico,rmvb,tar.gz,tar</p>
  </div>
</div>
<br>
<?php
//保证安全性，防止用户看见其他用户的文件
$user=$_COOKIE['key'];
//判断是否登录！
if($user=="") {
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
	echo '<script language="JavaScript">
  swal({
    title: "您还没有登录！",
    text: "您还没有登录！您需要登录才能使用！",
    icon: "error",
    button: "确定",
  })
  .then((willDelete) => {
      location.href="../login.php";
    }
  );
  </script>';

}else{
echo'<div class="card">
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="file">文件名：</label><span class="badge badge-primary">每次上传文件不可大于200000kb</span><br>
    <input type="file" name="file" id="file"><br><br>
    <button class="btn btn-primary" name="submit" type="submit">上传文件</button>
</form>
</div>
文件列表:<br>';
$user = $_COOKIE['key'];
$dir = './'.$user.'/';//-----------------S
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$dir_1 = $user.'/';
$handler = opendir($dir);
while (($f1 = readdir($handler)) !== false) {
	if ($f1 != "." && $f1 != "..") {
		$files[] = $f1;
	}
}
closedir($handler);
//打印所有文件名
$files[] = [];

foreach ($files as $filename) {//删除文件就这样吧！虽然bug，啊哈哈！
$path = $dir.$filename;//文件存储位置
$file_number = @filesize($path);  //获取文件大小

//检查文件大小是否为0
if($file_number != 0) {
$file_number_to_mb = round($file_number / (1024*1024),5);//单位换算
$creat_time = @filectime($path);//获取时间
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$date = date("Y-m-d H:i:s",$creat_time);//变量存储
  echo '<div class="card">
  <div class="card-body">
  <h5><a href='.$dir.$filename.'>'.$filename.'</a>|<span class="badge badge-info">文件大小</span>'.$file_number_to_mb.'MB|<span class="badge badge-info">创建日期</span>'.$date.'</h5>
  <p class="lead">
  请不要上传非法文件！长按复制链接即可分享和下载！</p>
  <form action="del.php" method="post">
<input type="text" name="name_file_post" value='.$filename.' readonly="readonly" style = "display:none">
<p align="right"><input type="submit" class="btn btn-danger" value="删除"></p>
  </form>
  </div>
</div>
<br>
';
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
}
}
}
?>
</div>

</div>


<?php
include '../footer.php';
include './function.php';
//就这样吧！
//----------------------
$install_file = "../config/install.ok";
if(is_file($install_file))
{}else{
header('Location: ../install/install.php');
exit;
}
?>

</body>
</html>