<!doctype html>
<html lang="CN">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>何乐云笔记</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="./markdown/css/editormd.min.css">
<link rel="Shortcut Icon" href="../image/title.ico" type="image/x-icon" />
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
<form method="post">
<input class="form-control" style="width:360px;" type="text" placeholder="请文章输入标题" name="title"/>
<hr>
	<div id="md-content" style="z-index: 1 !important;">
	    <textarea name="show" ></textarea>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.2/dist/jquery.min.js"></script>
	<!-- 引入js -->
	<script src="./markdown/editormd.min.js"></script>
	<script type="text/javascript">
       //初始化Markdown编辑器
	    var contentEditor;
	    $(function() {
	      contentEditor = editormd("md-content", {
	        width   : "100%",//宽度
	        height  : 500,//高度
	        syncScrolling : "single",//单滚动条
			  path    : "./markdown/lib/"//依赖的包路径
	      });
	    });
	</script>
<hr>
<center><input type="submit" class="btn btn-primary" name="submit" value="创建笔记"/></center>
</div>
</form>
<br>
</div>
</div>
</div>
<br>
<?php
include '../footer.php';
include '../upload/function.php';
// =  = 底 部 样 式 = =
?>
</body>
</html>

<?php
$user = $_COOKIE['key'];
$edit = $_POST["show"];
$title_name = $_POST["title"];
if($_POST["submit"])
{
$bijiid = md5(uniqid());
$markdown = $edit;
$date = "../markdate/".$user."/".$bijiid.".MD";
$date_file_name = "../markdate/".$user."/name/".$bijiid.".MD.title.MD";
file_put_contents($date,$markdown);
file_put_contents($date_file_name,$title_name);
echo '<script language="JavaScript">
  swal({
    title: "创建成功",
    text: "创建成功，返回首页查看",
    icon: "success",
    button: "确定",
  })
  .then((willDelete) => {
      location.href="./index.php";
    }
  );
  </script>';
}
if($user=="") {
//----------------------
//版权所有，侵权必究 -
//copyright (C) 2022 pengzekai -
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