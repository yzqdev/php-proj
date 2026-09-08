<html lang="CN">
<head>
<meta charset="utf-8"/>
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
<div class="jumbotron">
  <h1 class="display-3">404-ERROR</h1>
  <p class="lead">QWQ 页面找不到了</p>
  <hr class="my-4">
  <p>页面被删除或不存在或者网址输入有误/The page is deleted or does not exist or the URL was entered incorrectly</p>
  <p class="lead">
    <a class="btn btn-primary btn-lg" href="./index.php" role="button">返回首页/HOME</a>
  </p>
</div>
<?php
include './footer.php';
//----------------------
$install_file = "./config/install.ok";
if(is_file($install_file))
{}else{
header('Location: install/install.php');
exit;
}
?>
</body>
</html>