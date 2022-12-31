<?php
function zhuanhuan($byte)
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
{
    $KB = 1024;
    $MB = 1024 * $KB;
    $GB = 1024 * $MB;
    $TB = 1024 * $GB;
    if ($byte < $KB) {
        return $byte . "B";
    } elseif ($byte < $MB) {
        return round($byte / $KB, 2) . "KB";
    } elseif ($byte < $GB) {
        return round($byte / $MB, 2) . "MB";
    } elseif ($byte < $TB) {
        //----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
        return round($byte / $GB, 2) . "GB";
    } else {
        return round($byte / $TB, 2) . "TB";
    }
}

$if_safe = $_COOKIE['password'];
$user = $_COOKIE['key'];
if ($user == ""){
//----------------------
//版权所有，侵权必究
//copyright (C) 2022 pengzekai
//----------------------
}else{
$pass_path = "../pass/".$user;
$password_first = file_get_contents($pass_path."/mainkey.txt");
    if ($password_first != $if_safe){
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