<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>退出</title>
<script src="../js/sweetalert.min.js"></script>
</head>
<body>

</body>
</html>

<?php
include './function.php';
$user = $_COOKIE['key'];//获取cookie
$dir = "./".$user."/";//上传目录
// 允许上传的文件后缀
$allowedExts = array("sb3","sb2","sb","apk","cpp","asm","h","iso","img","reg","bat","docx","pptx","md","c","class","ttf","psd","dat","sh","log","flv","3gp","gif","jpeg","jpg","png","zip","rar","doc","txt","sql","js","7z","exe","mp3","mp4","wav","ppt","pdf","wps","ppt","dll","mdb","xml","xls","ico","rmvb","tar.gz","tar");
$temp = explode(".", $_FILES["file"]["name"]);
echo $_FILES["file"]["size"];
$extension = end($temp);     // 获取文件后缀名
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/pjpeg")
|| ($_FILES["file"]["type"] == "application/zip")
|| ($_FILES["file"]["type"] == "application/octet-stream")
|| ($_FILES["file"]["type"] == "video/avi")
|| ($_FILES["file"]["type"] == "audio/mpeg")
|| ($_FILES["file"]["type"] == "audio/wav")
|| ($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "application/pdf")
|| ($_FILES["file"]["type"] == "application/msaccess")
|| ($_FILES["file"]["type"] == "text/xml")
|| ($_FILES["file"]["type"] == "application/x-javascript")
|| ($_FILES["file"]["type"] == "application/msword")
|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
|| ($_FILES["file"]["type"] == "application/vnd.ms-powerpoint")
|| ($_FILES["file"]["type"] == "image/x-icon")
|| ($_FILES["file"]["type"] == "image/bmp")
|| ($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "application/java")
|| ($_FILES["file"]["type"] == "application/x-tar")
|| ($_FILES["file"]["type"] == "application/x-compressed")
|| ($_FILES["file"]["type"] == "application/x-sh")
|| ($_FILES["file"]["type"] == "image/png")
|| ($_FILES["file"]["type"] == "image/x-png"))
&& ($_FILES["file"]["size"] < (20480*10*10*10*10)) //204800000字节 = 200000kb
&& in_array($extension, $allowedExts))
{
  //----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
    if ($_FILES["file"]["error"] > 0)
    {
        echo "错误：: " .$_FILES["file"]["error"] . "<br>";
    }
    else
    {
        echo "上传文件名: " .$_FILES["file"]["name"] . "<br>";
        echo "文件类型: " .$_FILES["file"]["type"] . "<br>";
        $file_user_round = round($_FILES["file"]["size"]/(1024*1024),5); 
        echo "文件大小: ".$file_user_round." MB<br>";
        //echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";
        //------------------------------------------------------------------
        $now_size_file_number1 = $_FILES["file"]["size"] / (1024*1024);
        $yushu1 = round($now_size_file_number1,5);
        $dir21 = "../data/".$user;
        $uesr_mu1 = $dir21;
        $now_size_file1 = @file_get_contents($uesr_mu1."/now_user_number.txt");
        $now_size_file_number_go1 = $now_size_file1 + $yushu1;
        //------------------------------------------------------------------
        if($now_size_file_number_go1 < (1024*10) )
        {
        move_uploaded_file($_FILES["file"]["tmp_name"], $dir.$_FILES["file"]["name"]);
        echo '
<script language="JavaScript">
swal({
  title: "存储成功",
  text: "您的文件已经上传至我们的服务器",
  icon: "success",
  button: "确定",
})
.then((willDelete) => {
    location.href="./index.php";
  }
);
</script>';
        $now_size_file_number = $_FILES["file"]["size"] / (1024*1024);
        $yushu = round($now_size_file_number,5);
        $dir2 = "../data/".$user;
        $uesr_mu = $dir2;
        $now_size_file = @file_get_contents($uesr_mu."/now_user_number.txt");
        $now_size_file_number_go = $now_size_file + $yushu;
        @file_put_contents($uesr_mu."/now_user_number.txt",$now_size_file_number_go);
        //----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
        }else{
echo '<script language="JavaScript">
swal({
  title: "已经超出总容量",
  text: "你的存储已经超出总容量,请升级账号！",
  icon: "error",
  button: "确定",
})
.then((willDelete) => {
    location.href="./index.php";
  }
);
</script>';
        }

    }
}
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
else
{
echo '<script language="JavaScript">
swal({
  title: "非法的文件格式或大于上传容量",
  text: "我们不允许本次上传的文件格式和上传容量大小！",
  icon: "error",
  button: "确定",
})
.then((willDelete) => {
    location.href="./index.php";
  }
);
</script>';
}
?> 