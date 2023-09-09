<div class="fixed-top">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="../index.php"><?php 
$title=@file_get_contents("../config/title.txt");
echo $title;
?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="../index.php">首页 <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="../xieyi.php">用户协议</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./index.php">个人中心</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="../notecloud">云笔记</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        我的账号
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
        <?php
$user = $_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$icon=@file_get_contents("../data/".$user."/icon.txt");

$user_if = $_COOKIE['key'];
if($user_if == "")
{
  echo '<center><img width="130px" height="130px" class="img-thumbnail" src="../image/1.png"></center>';
}else{
  echo '<center><img width="130px" height="130px" class="img-thumbnail" src='.$icon.'></center>';
}
?>
<br>
<div class="card">
<div class="title"><span class="badge badge-light">昵称</span>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$nc=@file_get_contents("../data/".$user."/name.txt");
$user_if = $_COOKIE['key'];
if($user_if == "")
{
  echo "未登录";
}else{
  echo $nc;
}
?>

</div>
<div class="title"><span class="badge badge-light">QQ</span>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$qq=@file_get_contents("../data/".$user."/qq.txt");
$user_if = $_COOKIE['key'];
if($user_if == "")
{
  echo "未登录";
}else{
  echo $qq;
}
?></div>

<div class="title"><span class="badge badge-light">金币</span>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$glod=@file_get_contents("../data/".$user."/glod.txt");
$user_if = $_COOKIE['key'];
if($user_if == "")
{
  echo "未登录";
}else{
  echo $glod;
}
?></div>

<div class="title"><span class="badge badge-light">存储空间</span>
<?php
$user=$_COOKIE['key'];
header("content-type:text/html;charset=utf-8");
$chunchu =@file_get_contents("../data/".$user."/now_user_number.txt");
$user_if = $_COOKIE['key'];
if($user_if == "")
{
  echo "未登录";
}else{
  echo $chunchu."MB/10240MB(10GB)";
}
?>
<div class="dropdown-divider"></div>
          <?php $user_if = $_COOKIE['key'];if($user_if == ""){echo '<a class="dropdown-item" href="login.php">登录</a>';}else{}?>
          <?php $user_if = $_COOKIE['key'];if($user_if == ""){echo '<a class="dropdown-item" href="reg.php">注册</a>';}else{}?>
        <a class="dropdown-item" href="../exit.php">退出</a>
        </div>
        </div>
      </li>
    </ul>
  </div>
</nav>
</div>