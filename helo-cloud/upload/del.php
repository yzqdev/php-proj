<html lang="CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>何乐云盘 - 个人中心</title>
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
</body>
</html>
<?php
$user = $_COOKIE['key'];
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
if($user=="") {

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
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
$file_name = $_POST["name_file_post"];
$path = "./".$user."/".$file_name;
$file_number = filesize($path);
$file_number_to_mb = round($file_number / (1024*1024),5);
echo $file_number_to_mb."|";
$user_date = "../data/".$user;
$now_size_file = @file_get_contents($user_date."/now_user_number.txt");
$now_size_file_number_down = $now_size_file - $file_number_to_mb;
echo $now_size_file_number_down."|";
@file_put_contents($user_date."/now_user_number.txt",$now_size_file_number_down);
echo "yes";
unlink($path);
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
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
?>