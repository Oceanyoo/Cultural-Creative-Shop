<?php
session_start();
session_unset();    // 清除所有 SESSION 變數
session_destroy();  // 銷毀 session
header("Location: index.php"); // 返回首頁或登入頁
exit();
?>
