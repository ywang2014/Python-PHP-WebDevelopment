<html>
<body>

<?php

if (isset($_REQUEST['email_to']))
//if "email" is filled out, send email
{
	//send email
	
	require_once('smtp.php');
	require_once('config.email.php');
	
	$arr = explode('.', $smtpserver);
	$email_from = $smtpuser.'@'.$arr[1].'.'.$arr[2];
	// $email_from = '1071512072@qq.com'; ���������˷��ͣ�

	$email_to = $_REQUEST['email_to']; 
	$subject = $_REQUEST['subject'];
	$message = $_REQUEST['message'];
	
	$smtp = new smtp($smtpserver, $smtpport, true, $smtpuser, $smtppassword);
	//$smtp->debug = TRUE;

	/**
		arr���ն���ʼ������ߣ�foreach������һ��һ���ķ��ͼ��ɣ�
	*/
	
	$smtp->sendmail($email_to, $email_from, $subject, $message, $mailtype);
	
	echo "Thank you for using our mail form";

	header("refresh:1;url=email.php");
	print('���ڼ��أ����Ե�...<br>������Զ���ת~~~');
	// header�ض��� �͵ȼ������û��ڵ�ַ������url 
	// header('location:eamil.php');
}
else
{
	//if "email" is not filled out, display the form
	{ ?>
		<form method='post' action='email.php'>
		Email_To: <input name='email_to' type='text' /><br />
		Subject: <input name='subject' type='text' /><br />
		Message:<br />
		<textarea name='message' rows='15' cols='40'>
		</textarea><br />
		<input type='submit' />
		</form>
<?php }
} ?>
</body>
</html>