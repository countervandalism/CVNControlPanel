<?php
/**
 * pending.php :: View the commands queue
 *
 * @package CVN Control Panel
 * @created February 6th, 2010
 * @author Timo Tijhof <timotijhof@gmail.com>, 2010 - 2011
 * 
 * CVN Control Panel by Timo Tijhof is licensed under
 * the Creative Commons Attribution-Share Alike 3.0 Unported License
 * creativecommons.org/licenses/by-sa/3.0/
 */
require_once( '_init.php' );

loadSettings();
getServicesStatus();

?>
<!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 
	<meta charset="utf-8"> 
	<title>CVN | <?=$c['title']?> - Pending commands</title>
	<link rel="stylesheet" href="../main.css" type="text/css" media="all"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<link rel="stylesheet" href="cvnbots-cp.css" type="text/css" media="all"/>
</head>
<body>
	<div id="page-wrap" style="width: 90%; max-width: 1280px;">
		
		<h1><a href="<?=$c['cvntoolsbaseurl']?>"><small>CVN</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<hr/>
<?php require( '_menu.php' ); ?>
		<hr/>

		<h2>Pending commands</h2>
		<?php cvnCpScheduleRefresh(); ?>

		<p>Syntax used:</p>
		<ul>
			<li><code>kill|command</code></li>
			<li><code>start|directory|commmand</code></li>
		</ul>
		<p>The following commands are currently enqueued (oldest pending on top):</p>
		<pre class="wikitable"><?php
			getQueue();
		?></pre>

		<?php echo cvnCpGetLastShellSync(); ?>

		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
</body>
</html>
