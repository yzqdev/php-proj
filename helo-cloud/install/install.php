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
<div class="card">
<div class="card-body">
<div>
  <h3>HeloCloud Install File</h3>
  <div class="alert alert-primary" role="alert">
  欢迎使用Helo cloud云程序，好的，现在，让我们开始吧！
</div>
  <div class="jumbotron jumbotron-fluid">
  <div class="container">
    <p class="lead">服务器信息</p>
<?php
echo '系统和版本：'.php_uname().'<br>';
echo 'PHP运行方式：'.php_sapi_name().'<br>';
echo 'PHP版本号：'.PHP_VERSION.' [需大于PHP 5.8]<br>';
?>
  </div>
</div>
  <p class="lead">
    <a class="btn btn-primary btn-lg" href="./install_main.php" role="button">下一步</a>
  </p>
<br>
</div>
</div>
</div>
</div>

</center>



<?php
include '../footer.php';
include '../safety.php';
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
$install_file = "./install.ok";
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