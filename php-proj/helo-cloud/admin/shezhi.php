<!doctype html>
<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
//----------------------
$title=@file_get_contents("../config/title.txt");
echo $title.'- Powered by HeloCloud';
?></title>
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
<div class="container">
<div class="card">
<div class="card-body">
<form method="post"  autocomplete="off">
<div class="input-group flex-nowrap">
  <div class="input-group-prepend">
    <span class="input-group-text" id="addon-wrapping">设置标题</span>
  </div>
  <input type="text" class="form-control" placeholder="<?php 
$title=@file_get_contents("../config/title.txt");
echo $title;
?>" name="title" aria-describedby="addon-wrapping">
</div><br>
<input class="btn btn-primary btn-lg" type="submit" name="submit" value="保存"/><br>
</form>
</div>
</div>
</div>
<?php
include '../footer.php';
include './panduan.php';
$install_file = "../config/install.ok";
if(is_file($install_file))
{}else{
header('Location: install/install.php');
exit;
}
//----------------------
//----------------------
//版权-所有，侵权-必究---==
//copy-right (C) 2022 pe-ngz-ekai-
//----------------------
?>

</body>
</html>

<?php
$title_neme = $_POST["title"];
$title_file = "../config/title.txt";
if ($_POST["submit"]) {
    if ($title_neme == "") {
        echo '<script language="JavaScript">
    swal({
    title: "输入标题",
    text: "只有您输入正确输入标题后才可以保存",
    icon: "error",
    button: "确定",
    });
    </script>';
    }else{
    file_put_contents($title_file,$title_neme);
    echo '<script language="JavaScript">
    swal({
    title: "保存成功",
    text: "标题保存成功",
    icon: "success",
    button: "确定",
    });
    </script>';
    }
}
?>