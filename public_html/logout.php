<?php
/**
 * logout.php :: Logout procedure (unprotected page)
 *
 * @package CVN Control Panel
 * @created August 31th, 2010
 * @author Timo Tijhof <timotijhof@gmail.com>, 2010 - 2011
 * 
 * CVN Control Panel by Timo Tijhof is licensed under
 * the Creative Commons Attribution-Share Alike 3.0 Unported License
 * creativecommons.org/licenses/by-sa/3.0/
 */
$c['no_login'] = true;
require_once( '_init.php' );

/**
 *  Logging out...
 * -------------------------------------------------
 */
// Start session
session_start();

// Unset the variables stored in session
unset($_SESSION['SESS_USER']);

?>
<!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 
	<meta charset="utf-8"> 
	<title>CVN | <?=$c['title']?> - Logout</title>
	<link rel="stylesheet" href="../main.css?d2=<?=date('Y-m-d_H')?>h" type="text/css" media="all"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<link rel="stylesheet" href="cvnbots-cp.css" type="text/css" media="all"/>
</head>
<body id="login">
	<div id="page-wrap">
		
		<h1><a href="<?=$c['cvntoolsbaseurl']?>"><small>CVN</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<hr/>
		<p style="text-align: right;"><a href="login.php">Log in</a></p>
		<hr/>

		<h2>Log out</h2>
		<p>You've been logged out. Do you want to <a href="login.php">log back in</a>?</p>
		
		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
</body>
</html>
