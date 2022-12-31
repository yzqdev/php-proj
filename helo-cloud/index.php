<!doctype html>
<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php 
$title=@file_get_contents("./config/title.txt");
echo $title.'- Powered by HeloCloud';
?></title>
<link rel="stylesheet" href="./css/bootstrap.min.css">
<link rel="Shortcut Icon" href="./image/title.ico" type="image/x-icon" />
<script src="./js/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://strapdownjs.com/v/0.2/strapdown.js"></script>
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
<div class="jumbotron">
  <h1 class="display-4"><?php 
$title=@file_get_contents("./config/title.txt");
echo $title;
?></h1>
  <p class="lead">上传你的文件，创建你的笔记，发出你的灵感，然后，开启新世界的大门！</p>
  <hr class="my-4">
  <p>Upload your files, create your notes, send out your inspiration, and then, open the door to a new world!</p>
  <br>
  <a class="btn btn-primary btn-lg" href="./notecloud" role="button">创建笔记/creat a note</a><br><br>
  <a class="btn btn-primary btn-lg" href="./upload" role="button">创建文件/upload a file</a>
</div>
<br>

</center>
<?php
include './footer.php';
include './safety.php';

$install_file = "./config/install.ok";
if(is_file($install_file))
{}else{
header('Location: install/install.php');
exit;
}
//----------------------
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
?>

</body>
</html>
