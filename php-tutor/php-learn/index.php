<?php
#连接数据库
$conn = mysqli_connect('localhost', 'root', '123456', 'php_db');
#设置编码
mysqli_query($conn, 'set names utf8');

// 创建数据库
$sql = "CREATE DATABASE  if not exists php_db";
if ($conn->query($sql) === TRUE) {
    echo "数据库创建成功";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
echo ("hehhhdsf");
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
    echo 'You are using Firefox.<br />';
}
