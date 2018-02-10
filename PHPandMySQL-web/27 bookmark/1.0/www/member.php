<?php
/*
	验证用户身份
	显示与用户相关的书签
*/

require_once('bookmark_fns.php');

session_start();

$username = trim($_POST['username']);
$password = $_POST['password'];

if ($username && $password)
{
	try
	{
		login($username, $password);
		$_SESSION['valid_username'] = $username;
	}
	catch (Exception $e)
	{
		do_html_heading('Problem: ');
		
		echo "You could not be logged in. You must be logged in to view this page.";
		
		do_html_rul('login.php', 'Login');
		
		do_html_footer();
		
		exit;
	}
}

do_html_header('Home');
check_valid_user();

if ($url_array = get_user_urls($_SESSION['valid_user']))
{
	display_user_urls($url_array);
}

display_user_menu();

do_html_footer();

?>