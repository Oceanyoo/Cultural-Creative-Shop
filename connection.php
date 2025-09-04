<?php 
$host="localhost";//主機名稱
$user="root";//使用者名稱
$pass="root123456";//資料庫密碼
$dbname="group_16";//要使用的資料庫名稱

$conn=new mysqli($host,$user,$pass,$dbname);
if($conn->connect_error)
{
    die("連線失敗:".$conn->connect_error);//如果有錯die()函式會立刻中止函式，並顯示錯誤訊息
}

// 加這一行，設定正確的編碼
$conn->set_charset("utf8mb4");

?>