<?php
/**
 * control.php :: Starting and killing the bots
 *
 * @package CVN Control Panel
 * @created August 31th, 2010
 * @author Timo Tijhof <timotijhof@gmail.com>, 2010 - 2011
 * 
 * CVN Control Panel by Timo Tijhof is licensed under
 * the Creative Commons Attribution-Share Alike 3.0 Unported License
 * creativecommons.org/licenses/by-sa/3.0/
 */
require_once( '_init.php' );

// Get URL-parameters
$s['bot'] = !empty($_GET['bot']) ? trim($_GET['bot']) : FALSE;
$s['action'] = !empty($_GET['action']) ? trim($_GET['action']) : FALSE;
$s['pid'] = !empty($_GET['pid']) ? trim($_GET['pid']) : FALSE;
$s['ccp_once'] = 'error'; // will be overridden if success

// Load it and get the status
loadSettings();
getServicesStatus();
	
// Checks
if( !$s['bot'] ){
	die("Control error:<br/>\nInvalid botname");
}
if( $c['bots'][$s['bot']][5] == 1 ){
	die("Access denied:<br/>\nYou can't control ".$s['bot']);
}

?>
<!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 
	<meta charset="utf-8"> 
	<title>CVN | <?=$c['title']?> - Controlling <?=$s['bot']?></title>
	<link rel="stylesheet" href="../main.css" type="text/css" media="all"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<link rel="stylesheet" href="cvnbots-cp.css" type="text/css" media="all"/>
</head>
<body>
	<div id="page-wrap">
		
	<h1><a href="<?=$c['cvntoolsbaseurl']?>"><small>CVN</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<hr/>
<?php require( '_menu.php' ); ?>
		<hr/>
		
		<h2>Controlling <?=$s['bot']?></h2>
<?php

// Log this
file_put_contents(
	$c['logdir'].'CVNBotsControlPanel.log'
	,
	'['.date('r').'] '
		. $s['action'].' '.$s['bot'].($s['pid'] ? " ({$s['pid']})" : '')
		. ' - ' . $_SESSION['SESS_USER']
		. ' (' . $_SERVER['REMOTE_ADDR'] . '; ' . gethostbyaddr( $_SERVER['REMOTE_ADDR'] ).'; ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . ')'
		. "\n"
	,
	FILE_APPEND
);

switch( $s['action'] ){
	case 'start':
		if ($c['bots'][$s['bot']][1] !== FALSE) {
			echo "<h3>Error</h3><p>".$s['bot']." is already running!</p>";
		} else{
			echo '<h3>Starting...</h3>';
			
			// Command
			$shell_chdir = $c['bots'][$s['bot']][3];

			$shell_exec = $c['bots'][$s['bot']][0] .' '. $c['bots'][$s['bot']][4] .' 1>'. $c['logdir' ].$s['bot'] .'.log 2>&1';
			
			// Queue it
			$fCmd = fopen($c['commands_queue'], 'a'); // Open the commands queue
			flock($fCmd, LOCK_EX);  // Lock the file exclusively
			fwrite($fCmd, "\n" . "start|" . $shell_chdir . "|" . $shell_exec);
			fclose($fCmd); // Close and release the file

			// Report back to frontend
			echo '<p>Added to the start-command queue:</p>';
			echo '<pre>chdir:<br/>'.$shell_chdir.'</pre>';
			echo '<pre>exec:<br/>'.$shell_exec.'</pre>';
			$s['ccp_once'] = "success";
		}
		break;
	case 'kill':
		if (!is_numeric($s['pid']))
			echo '<h3>Error</h3><p>Invalid PID: '.$s['pid'].'</p>';
		else {
			//Let's check the PID belongs to the bot
			$pidMatch = false;
			foreach ( $c['bots'][$s['bot']][1] as $cbotpid ) {
				if ( $cbotpid == $s['pid'] ) {
					$pidMatch = true;
				}
			}
			if ( !$pidMatch ) {
				echo '<h3>Error</h3><p>PID mismatch: '.$s['pid'].' does not belong to '.$s['bot'].'</p>';
			} else {
				echo '<h3>Killling...</h3>';
			
				// Command
				$shell_exec = 'kill -KILL '.$s['pid'];
				
				// Queue it
				$fCmd = fopen($c['commands_queue'], 'a'); // Open the commands queue
				flock($fCmd, LOCK_EX);  // Lock the file exclusively
				fwrite($fCmd, "\n" . "kill|" . $shell_exec);
				fclose($fCmd); // Close and release the file

				// Report back to frontend
				echo '<p>Added to the kill-command queue:</p>';
				echo '<pre>exec:<br/>'.$shell_exec.'</pre>';
				$s['ccp_once'] = "success";
			
			}
		}
		break;
	case 'killall':
		//Let's get the PIDs belonging to the bot
		$pidMatches = array();
		$pidMatches = $c['bots'][$s['bot']][1];
		if ( empty($pidMatches) ) {
			echo '<h3>Error</h3><p>No of '.$s['bot'].' found</p>';
		} else {
			echo '<h3>Killling all of '.$s['bot'].'...</h3>';
		
			$shell_execes = array();
			foreach ( $pidMatches as $pidMatch ) {
				// Command
				$shell_execes[] = 'kill -KILL '.$pidMatch;
			}
				
			// Queue it
			$fCmd = fopen($c['commands_queue'], 'a'); // Open the commands queue
			flock($fCmd, LOCK_EX);  // Lock the file exclusively
			foreach ( $shell_execes as $shell_exec ) {
				fwrite($fCmd, "\n" . "kill|" . $shell_exec);
			}
			fclose($fCmd); // Close and release the file

			// Report back to frontend
			echo '<p>Added to the kill-command queue:</p>';
			echo '<pre>exec:<br/>'.implode("\n", $shell_execes).'</pre>';
			$s['ccp_once'] = "success";
		
		}
		break;
	default:
		echo '<h3>Error</h3><p>Unknown action: '.$s['action'].'</p>';
}
	$c['redirecturl'] = $c['baseurl']. '?bot='. $s['bot'] .'&action=' .$s['action']. '&pid='. $s['pid']. '&ccp_once='. $s['ccp_once'];
	echo '<p><a href="'.$c['redirecturl'].'">&laquo; Return</a> (automatically after 5 seconds)</p>';
?>
		
		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
<script>
function refresh(){
	window.location.href = '<?=$c['redirecturl']?>&timestamp='+new Date(new Date().toUTCString())/1000;
}
setTimeout(refresh, 4500);
</script>
</body>
</html>
