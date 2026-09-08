<!DOCTYPE html>
<html lang="CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php 
$title=@file_get_contents("../config/title.txt");
echo $title.'云笔记 - Powered by HeloCloud';
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

<div class="jumbotron jumbotron-fluid">
  <div class="container">
    <h1 class="display-4">Note Cloud</h1>
    <p class="lead">在这里完成你的创意，随时书写，云端保存，云笔记中心 - FOR YOU & FOR EVERYONE</p>
  </div>
</div>

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
$user = $_COOKIE['key'];
$qq = @file_get_contents("../data/".$user."/qq.txt");
echo $qq;
?></div>

<div class="title"><span class="badge badge-light">金币</span>
<?php
$user = $_COOKIE['key'];
$glod = @file_get_contents("../data/".$user."/glod.txt");
echo $glod;
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
<br>
<?php
//保证安全性，防止用户看见其他用户的文件
$user = $_COOKIE['key'];

if($_POST["wen_na_go"])
{
$file_n = $_POST["wen_na_go"];
$path_1 = "../markdate/".$user."/".$file_n;
$path_2 = "../markdate/".$user."/name/".$file_n.".title.md";
unlink($path_1);
unlink($path_2);
echo '
      <script language="JavaScript">
      swal({
      title: "删除成功！",
      text: "您已经成功删除文件！",
      icon: "success",
      button: "确定",
      })
      .then((willDelete) => {
        location.href="./";
      }
      );
      </script>';
}

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
<form action="edit.php" method="post" enctype="multipart/form-data">
<button class="btn btn-primary" name="creat" type="submit">创建笔记</button>
<a class="btn btn-primary" href="../upload" role="button">创建文件</a><br><br>
</form>
</div>
<h4>文章列表:</h4><br>';
$user = $_COOKIE['key'];
$dir = '../markdate/'.$user.'/';//-----------------S
$dir_as = '../markdate/'.$user.'/name';//-----------------S
if(is_dir($dir))
{
}
else
{
  mkdir($dir,0777,true);
  mkdir($dir_as,0777,true);//dd
}
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//万物皆有灵！
//----------------------
$dir_1 = '../markdate/'.$user.'/';//目录
$handler = opendir($dir);
while (($f1 = readdir($handler)) !== false) {
	if ($f1 != "." && $f1 != "..") {
		$files[] = $f1;
	}
}
closedir($handler);
//打印所有文件名
$files[] = [];

foreach ($files as $filename) {
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
$jd = $dir.'name/'.$filename.".title.md";//=========================
$name_give = file_get_contents($jd);
$date = date("Y-m-d H:i:s",$creat_time);//变量存储
  echo '<div class="card">
  <div class="card-body">
  <h5><a href="give.php?article_id='.$filename.'&user='.$user.'">'.$name_give.'</a>|<span class="badge badge-info">创建日期</span>'.$date.'</h5>
  <form action="index.php" method="post">
<input type="text" name="wen_na_go" value='.$filename.' readonly="readonly" style = "display:none">
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
include '../upload/function.php';
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
?>

</body>
</html>