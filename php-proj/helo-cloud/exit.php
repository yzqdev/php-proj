<!-- 
文件描述 ：登出
copyright (C) 2021 pengzekai
请尊重版权，本文件遵守何乐开源协议
 -->
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

</body>
</html>
<?php
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
setcookie("key"/*cookie的名称*/,""/*cookie的值*/,""/*cookie的生效时间*/);
setcookie("password"/*cookie的名称*/,""/*cookie的值*/,""/*cookie的生效时间*/);
$install_file = "./config/install.ok";
if(is_file($install_file))
{}else{
header('Location: install/install.php');
exit;
}

if($user==""){
echo '<script language="JavaScript">
swal({
  title: "已经退出登录",
  text: "已经退出登录！只有您登录后您才能使用网盘",
  icon: "error",
  button: "确定",
})
.then((willDelete) => {
    location.href="./index.php";
  }
);
</script>';
}
exit();
?>