<?php
/*
	启动会话，并注册变量
*/
session_start();

$_SESSION['sess_var'] = 'Hello world!';

echo "<h1> Session Start </h1>";

echo 'The content of $_SESSION[\'sess_var\'] is '.$_SESSION['sess_var'].'<br>';

?>

<a href = "page2.php"> Next page </a>