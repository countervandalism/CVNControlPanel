<?php
/**
 * control_log.php :: View the control log
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
	<title>CVN | <?=$c['title']?> - Control log</title>
	<link rel="stylesheet" href="../main.css" type="text/css" media="all"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<link rel="stylesheet" href="cvnbots-cp.css" type="text/css" media="all"/>
</head>
<body>
	<div id="page-wrap" style="width: 90%; max-width: 1200px;">		
		<h1><a href="<?=$c['cvntoolsbaseurl']?>"><small>CVN</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<hr/>
<?php require( '_menu.php' ); ?>
		<hr/>

		<h2>Control log</h2>
		<pre class="wikitable" style="min-width: 1150px;"><?php
			echo '<p style="text-align: center;">' . krEscapeHTML(
				'Logged in as '
				. $_SESSION['SESS_USER']
				. ' ('.$_SERVER['REMOTE_ADDR'].'; '.gethostbyaddr($_SERVER['REMOTE_ADDR']).'; '.$_SERVER['HTTP_X_FORWARDED_FOR'].')'
			) . "<hr/>\n";
			
			$log = @file_get_contents( $c['logdir'].'CVNBotsControlPanel.log' );
			if ( $log ) {
				// Make bot names align nicely
				// start   Foo
				// kill    Foo
				// killall Foo
#				$log = str_replace( 'killall ', 'killall ', $log );
				$log = str_replace( 'start '  , 'start   ', $log );
				$log = str_replace( 'kill '   , 'kill    ', $log );
				$log = krEscapeHTML(
					// Reverse order to make it descending (by date)
					implode( "\n", array_reverse( explode( "\n", 
						$log
					) ) )
				);
			} else {
				$log = '<p style="text-align: center;"><em>Control log is empty.</em></p>';
			}
			echo $log;
		?></pre>

		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
</body>
</html>
