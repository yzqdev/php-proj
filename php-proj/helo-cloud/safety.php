<?php
$if_safe = $_COOKIE['password'];
$user = $_COOKIE['key'];
if ($user == ""){
}else{
$pass_path = "./pass/".$user;
$password = file_get_contents($pass_path."/mainkey.txt");
    if ($password == $if_safe){
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
    }else{
echo '<script language="JavaScript">
swal({
  title: "非法登录",
  text: "程序安全中心提示：非法登录！",
  icon: "error",
  button: "确定",
});
</script>';
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
setcookie("key"/*cookie的名称*/,""/*cookie的值*/,""/*cookie的生效时间*/);
setcookie("password"/*cookie的名称*/,""/*cookie的值*/,""/*cookie的生效时间*/);
exit();
    }
}
?>