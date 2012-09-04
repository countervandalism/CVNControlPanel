<?php
/**
 * login.php :: Login form (unprotected page)
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
 *  Login
 * -------------------------------------------------
 */

$s['login_preform_html'] = '<!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 
	<meta charset="utf-8"> 
	<title>CVN | '.$c['title'].' - Login</title>
	<link rel="stylesheet" href="../main.css?d2='.date('Y-m-d_H').'h" type="text/css" media="all"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<link rel="stylesheet" href="cvnbots-cp.css" type="text/css" media="all"/>
</head>
<body id="login">
	<div id="page-wrap">
		
		<h1><a href="'.$c['cvntoolsbaseurl'].'"><small>CVN</small></a> | <a href="'.$c['baseurl'].'">'.$c['title'].'</a></h1>
		<hr/>
';

$s['login_afterform_html'] = '
		
		<hr id="footer"/>
		<address>'.$c['title'].' '.$c['revID'].' ('.$c['revDate'].') on '.$_SERVER['HTTP_HOST'].'</address>
</div>
</body>
</html>
';

require_once('../login.inc.php');
