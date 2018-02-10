<?php
/*
	系统框架--一个脚本控制所有的操作
*/

/*****************************************************************************************************
	Stage 1: pre-processing
	Do any required processing before page header is sent
	and decide what details to show on page headers
*****************************************************************************************************/

include ("include_fns.php");
session_start();

// create short variable name
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$account = isset($_POST['account']) ? trim($_POST['account']) : '';
$messageid = isset($_POST['messageid']) ? trim($_POST['messageid']) : '';

$to = isset($_POST['to']) ? trim($_POST['to']) : '';
$cc = isset($_POST['cc']) ? trim($_POST['cc']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$cc = isset($_POST['message']) ? trim($_POST['message']) : '';

$buttons = array();

// append to this string if anything processed before header has output 
$status = '';

// need to process log in or out requests before anything else 
if ($username || $passeword)	// &&
{
	if (login($username, $password))
	{
		$status .= "<P style = \"padding-bottom:100px;\" > Logged in successfully. </p>";
		$_SESSION['auth_user'] = $username;
		if (number_of_accounts($_SESSION['auth_user']) == 1)
		{
			$accounts = get_account_list($_SESSION['auth_user']);
			$_SESSION['selected_account'] = $accounts[0];
		}
	}
	else
	{
		$status .= "<p style = \"padding-bottom:100px;\" > Sorry, we could not log you in with that username and password. </p>"
	}
}

if ($action == "log_out")
{
	session_destroy();
	unset($action);
	$_SESSION = array();
}

// need to process choose, delete or store account before drawing header 
switch ($action)
{
	case "delete_account":
		delete_account($_SESSION['auth_user'], $account);
		break;
		
	case "store_settings":
		store_account_settings($_SESSION['auth_user'], $_POST);
		break;
		
	case "select_account":
		// if have choose a valid account, store it as a session variable 
		if (($account) && (account_exists($_SESSION['auth_user'], $account)))
		{
			$_SESSION['selected_account'] = $account;
		}
		break;
}

// set the buttons that will be on the tool bar
$buttons[0] = "view_mailbox";
$buttons[1] = "new_message";
$buttons[2] = "account_setup";

// only offer a log out button if logged in 
if (check_auth_user())
{
	$buttons[4] = "log_out";
}

/****************************************************************************************
	Stage 2: headers
	Send the HTML headers and menu bar appropriate to current action 

****************************************************************************************/
if ($action)
{
	do_html_header($_SESSION['auth_user'], "Warm Mail - ".format_action($action), $_SESSION['selected_account']);
}
else
{
	// display header with just application name 
	do_html_header($_SESSION['auth_user'], "Warm Mail", $_SESSION['selected_account']);
}

display_toolbar($buttons);

/****************************************************************************************
	Stage 3: body
	Depending on action, show appropriate main body content
****************************************************************************************/

// dispaly any text generated by functions called before header 
echo $status;

if (!check_auth_user())
{
	echo "<p> You need to log in";
	
	if ($action && $action != "log_out") 
	{
		echo "to go to ".format_action($action);
	}
	
	echo ". </p>";
	
	display_login_form($action);
}
else
{
	switch ($action)
	{
		// if we have chosen to setup a new account, or have just added or deleted an account, show account setup page.
		case "store_settings":
		case "account_setup":
		case "delete_account":
			display_account_setup($_SESSION['auth_user']);
			break;
			
		case "send_message":
			if (send_message($to, $cc, $subject, $message))
			{
				echo "<p style = \"padding-bottom:100px;\" > Message Sent. </p>";
			}
			else
			{
				echo "<p style = \"padding_bottom:100px;\" > Could not send message. </p>";
			}
			break;
			
		case "delete":
			delete_message($_SESSION['auth_user'], $_SESSION['selected_account'], $messageid);
			
			// note deliberately no "break" - we will continue to the next case 
			
		case "select_account":
		
		case "view_mailbox":
			// if mailbox just chosen, or view mailbox chosen, show mailbox 
			display_list($_SESSION['auth_user'], $_SESSION['selected_account']);
			break;
			
		case "show_headers":
		case "hide_headers":
		case "view_message":
			// if we have just picked a message from the list, or were looking at a message and chose to hide or view headers, load a message 
			$fullheaders = ($action == 'show_headers');
			display_message($_SESSION['auth_user'], $_SESSION['select_account'], $messasged, $fullheaders);
			break;
		
		case 'reply_all':
			// set cc as old cc line 
			if (!$imap)
			{
				$imap = open_mailbox($_SESSION['auth_user'], $_SESSION['selected_account']);
			}
			
			if ($imap)
			{
				$header = imap_header($imap, $messageid);
				if ($header->reply_toaddress)
				{
					$to = $header->reply_toaddress;
				}
				else
				{
					$to = $header->fromaddress;
				}
				
				$cc = $header->ccaddress;
				$subject = "Re: ".$header->subject;
				$body = add_quoting(stripslashes(imap_body($imap, $messageid)));
				imap_close($imap);
				
				display_new_message_form($_SESSION['auth_user'], $to, $cc, $subject, $body);
			}
			
			break;
			
		case "reply":
			// set to address as reply-to or from of the current message 
			if (!$imap)
			{
				$imap = open_mailbox($_SESSION['auth_user'], $_SESSION['selected_account']);
			}
			
			if ($imap)
			{
				$header = imap_header($imap, $messageid);
				if ($header->reply_toaddress)
				{
					$to = $header->reply_toaddress;
				}
				else
				{
					$to = $header->fromaddress;
				}
				$subject = "Re: ".$header->subject;
				$body = add_quoting(stripslashes(imap_body($imap, $messageid)));
				imap_close($imap);
				
				display_new_message_form($_SESSION['auth_user'], $to, $cc, $subject, $body);
			}
			break;
			
		case "forward":
			// set message as quoted body of current message 
			if (!$imap)
			{
				$imap = open_mailbox($_SESSION['auth_user'], $_SESSION['selected_account']);
			}
			
			if($imap)
			{
				$header = imap_header($imap, $messageid);
				$body = add_quoting(stripslashes($imap_body($imap, $messageid)));
				$subject = "Fwd: ".$header->subject;
				imap_close($imap);
				
				display_new_message_form($_SESSION['auth_user'], $to, $cc, $subject, $body);
			}
			break;
			
		case "new_message":
			display_new_message_form($_SESSION['auth_user'], $to, $cc, $subject, $body);
			break;
	}
}

/****************************************************************************************
	Stage 4: footer
****************************************************************************************/

do_html_footer();

?>