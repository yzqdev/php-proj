<!doctype html>
<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>云笔记 - 文章查看</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="./markdown/css/editormd.min.css">
<link rel="Shortcut Icon" href="../image/title.ico" type="image/x-icon" />
<script src="../js/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="./Markdown.Converter.js"></script>
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
<div class="alert alert-primary" role="alert">
  <CENTER>HeloCloud Markdown TextView V1.2 & MADE BY PENGZEKAI<CENTER>
</div>

<article style="display:none;">
<?php
$user = $_GET["user"];
$article_id = $_GET["article_id"];
$user_date = "../markdate/".$user."/".$article_id;
$nei = @file_get_contents($user_date);
if($user == "")
{
echo '<script language="JavaScript">
  swal({
    title: "用户为空",
    text: "用户为空！！！",
    icon: "error",
    button: "确定",
  })
  .then((willDelete) => {
      location.href="./index.php";
    }
  );
  </script>';
}else if($article_id == ""){
    echo '<script language="JavaScript">
    swal({
      title: "文章id为空",
      text: "文章id为空！！！",
      icon: "error",
      button: "确定",
    })
    .then((willDelete) => {
        location.href="./index.php";
      }
    );
    </script>';
}else if($nei == ""){
    echo '<script language="JavaScript">
    swal({
      title: "内容为空",
      text: "文章内容为空！！！",
      icon: "error",
      button: "确定",
    })
    .then((willDelete) => {
        location.href="./index.php";
      }
    );
    </script>';
}
echo $nei;
?>
</article >
<div id="out">
</div>

<script type="text/javascript">
    var target = document.querySelector("article")
    var c = new Markdown.Converter()
    var html = c.makeHtml(document.querySelector("article").innerText)
    document.getElementById('out').innerHTML = html
</script>
<br>
<hr>
<br>
<div class="jumbotron jumbotron-fluid">
  <div class="container">
    <center>
    <p class="lead">您可以分享这个页面的内容，但是，一旦分享，我们将不再保证本文本的安全性，请知悉！</p>
    <center>
  </div>
</div>
</div>
</div>
</div>
<hr>
<?php
include '../footer.php';
include '../upload/function.php';

?>
</body>
</html>

<?php
$user = $_COOKIE['key'];
$edit = $_POST["show"];

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
}

?>