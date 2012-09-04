<?php
/**
 * index.php :: Front-end
 *
 * @package CVN Control Panel
 * @created August 31th, 2010
 * @author Timo Tijhof <timotijhof@gmail.com>, 2010 - 2011
 * 
 * CVN Control Panel by Timo Tijhof is licensed under
 * the Creative Commons Attribution-Share Alike 3.0 Unported License
 * creativecommons.org/licenses/by-sa/3.0/
 */
error_reporting(-1);
require_once( '_init.php' );

// Parameters
$s['ccp_once'] = !empty($_GET['ccp_once']) ? strtolower(trim($_GET['ccp_once'])) : FALSE; 
$s['bot'] = !empty($_GET['bot']) ? trim($_GET['bot']) : FALSE;
$s['pid'] = !empty($_GET['pid']) ? trim($_GET['pid']) : FALSE;
$s['action'] = !empty($_GET['action']) ? trim($_GET['action']) : FALSE;

// Make sure timestamp is present on ccp_once
if( $s['ccp_once'] && $s['bot'] && $s['action'] && empty($_GET['timestamp']) ){
	header('Location: '.$c['baseurl']. '?bot='. $s['bot'] .'&action=' .$s['action']. '&pid='. $s['pid']. '&ccp_once='. $s['ccp_once'] .'&timestamp='. time());
	die("Redirecting...");
}

// Minimal length a ccp_once message should stay visible in seconds
$s['message_minage'] = 10;

// How many seconds old the last status update may be before
// a warning stats to appear
$s['ps_dump_maxage'] = 15 * 60;

loadSettings();
getServicesStatus();

?><!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 
	<meta charset="UTF-8">
	<title>CVN | <?=$c['title']?> - Welcome <?=ucfirst($_SESSION['SESS_USER'])?> !</title>
	<link rel="stylesheet" href="../main.css?2">
	<link rel="stylesheet" href="cvnbots-cp.css?2">
	<script>
	function confirmStart(botname) {
		if ( confirm('Start '+botname+'?')) {
			clearTimeout(window.CVN_refreshTimeout);
			window.location.href = '<?=$c['baseurl']?>control.php?bot='+botname+'&action=start';
		}
	}
	function confirmKill(botname,pid) {
		if ( confirm('The "Kill" command is a forced kill. It is recommended only as a last resort'
		+' when all else has failed.\n\nAre you sure you want to kill '+botname+' (PID '+pid+')?')) {
			clearTimeout(window.CVN_refreshTimeout);
			window.location.href = '<?=$c['baseurl']?>control.php?bot='+botname+'&action=kill&pid='+pid;
		}
	}
	function confirmKillAll(botname) {
		if ( confirm('The "KillAll" command is a mass forced kill. It is recommended only as a last resort'
		+' when all else has failed.\n\nAre you sure you want to kill *ALL* instances '+botname+'?')) {
			clearTimeout(window.CVN_refreshTimeout);
			window.location.href = '<?=$c['baseurl']?>control.php?bot='+botname+'&action=killall';
		}
	}
	</script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
</head>

<body>
	<div id="page-wrap">
		
	<h1><a href="<?=$c['cvntoolsbaseurl']?>"><small>CVN</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<hr/>
<?php require( '_menu.php' ); ?>
		<hr/>

<?php
// Handle ccp_once - only show message if timestamp is less then {message_minage} seconds old
// If it's older then the current page is not a direct result of the control-action and message is not important
$timestamp = time() - $s['message_minage'];
$refreshtxt = '';
if( $s['ccp_once'] && $s['bot'] && $s['action'] && $_GET['timestamp'] > $timestamp ){
	// Message
	echo '<div class="msg ns ccp_once '.$s['ccp_once'].'">'
		. 'Action "'.$s['action'].'" for '.$s['bot']. ' will execute shortly...'
		. '.</div>';
}
$timestamp = time() - $s['ps_dump_maxage'];
if ( $c['last_ps_update'] < $timestamp ) {
	// Message 
	$maxAge = intval( $s['ps_dump_maxage'] );
	
	krError('Job-queue is unresponsive (no response for over ' . ceil($maxAge/60) . ' minutes).');
}
?>		
		<div class="warning-msg">
			<img src="//upload.wikimedia.org/wikipedia/commons/thumb/0/03/Dialog-information.svg/50px-Dialog-information.svg.png" width="50" alt="" title="!" align="left"/><p>Please beware that commands may take up to 1-2 minutes to finish. To avoid collision: check <a href="#whoisonline">who is online</a> and make sure a similar action isn't in <a href="pending.php">the queue</a> already.</small></p>
			<p style="text-align: right; font-size: smaller;"><a href="pending.php">View the commands queue &raquo;</a></p>
		</div>
		<?php echo krError('You need to enable JavaScript in order to use this tool.', 0, 'non-js'); ?>
		
		<h3 id="status">Bot status</h3>

			<?php cvnCpScheduleRefresh($c['baseurl']); ?>
			<p style="font-size: smaller; text-align: right;">
			<?php echo cvnCpGetLastShellSync(); ?>
			</p>
			<table class="wikitable">
<?php

# Following are god aweful hacks
# Be careful!

$groupSWMTBots = '';
$groupOther = '';
$groupDisabled = '';

foreach ( $c['bots'] as $botname => $botvars ) {
	// Single instance of bot running (most common)
	if ( is_array($botvars[1]) && count($botvars[1]) === 1 ) {

		$pid = $botvars[1][0];
		$status = '<strong>Running</strong> ('.$pid.') since '.$botvars[2][0];
		$disabled = $botvars[5];
		$comment = $botvars[6];
		$statusdetectpath = $botvars[7];
		$action = 'kill '.$botname;
		$js = "confirmKill('$botname', '$pid');";
		$btnStyle = 'text-shadow: 0 0 10px rgb(255, 70, 70);';

	// Multiple instances running
	} elseif ( is_array($botvars[1]) && count($botvars[1]) > 1  ) {

		$status = array();
		foreach ( $botvars[1] as $botnum => $botnumvar ) {
			$pid = $botvars[1][$botnum];
			$status[] = '<strong>Running</strong> ('.$pid.') since ' . $botvars[2][$botnum]
				 . ' <input type="button" onclick="confirmKill(\'' . $botname. '\', \'' . $pid . '\')" value="kill"/>';
			$disabled = $botvars[5];
			$comment = $botvars[6];
		$statusdetectpath = $botvars[7];
			$action = 'killAll ' . $botname;
			$js = "confirmKillAll('$botname');";
		}
		$status = implode('<br/>', $status);
		$btnStyle = 'text-shadow: 0 0 10px rgb(255, 70, 70);';

	// Bot is not running
	} else {

		$pid = '';
		$status = '<strong>Not running</strong>';
		$disabled = $botvars[5];
		$comment = $botvars[6];
		$statusdetectpath = $botvars[7];
		$action = 'start '.$botname;
		$js = "confirmStart('$botname');";
		$btnStyle = 'text-shadow: 0 0 10px rgb(70, 255, 70);';
	}

	$row =
		'<tr><th>'
		. htmlspecialchars( $botname )
		. '<br/><span style="font-weight: normal; font-style: italic; font-size: small;">(<a href="console_output.php?bot='
		. htmlspecialchars( rawurldecode( $botname ) )
		. '">console output</a>)</span></th>'
		. '<td><small>';

	// Ugly comment stuff
	$comment = str_replace( '@@', '&quot;', $comment );
	$comment = htmlspecialchars( $comment );
	$comment = str_replace( '[isBackup]', '<b style="color: green;">[isBackup]</b>', $comment );

	$row .=
		$comment
		. '</small><br/>'
		. $status
		. '</td>'
		. '<td class="button">';

	if ( $disabled === '0' ) {
		$button = '<input type="button" style="' . htmlspecialchars( $btnStyle ) . '" onclick="' . htmlspecialchars( $js ) . '" value="' . htmlspecialchars( $action ) . '"/>';

	} elseif ( $disabled === '1' ) {
		$button = '<input type="button" disabled="disabled" value="disabled"/>';

	} else { /*( $disabled === '2' )*/
	 	$button = '<input type="button" disabled="disabled" value="access denied"/><br/><small><em>can\'t '
	 		. ( !empty($pid)  ? 'kill process' : 'start task' )
	 		. '</em></small><br/><strong>'
	 		. htmlspecialchars( $disabled )
	 		. '</strong>';
	}

	$row .=
		$button
		. '</td></tr>';


	// Decide output group in the overview
	if ( $disabled !== '0' ) {
		$groupDisabled .= $row;
	} elseif ( strpos($statusdetectpath, 'SWMTBot.exe') !== false ) {
		$groupSWMTBots .= $row;
	} else {
		$groupOther .= $row;
	}

}

echo
	'<tr><th colspan="3">SWMTBots</th></tr>'
	. $groupSWMTBots
	. '<tr><th colspan="3">Other bots</th></tr>'
	. $groupOther
	. '<tr><th colspan="3">Disabled bots</th></tr>'
	. $groupDisabled;

?>

			</table>
			
			
		<h3 id="whoisonline">Online users:</h3>
			<?php echo krGetWhoisOnline(); ?>

		
		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
<script>
jQuery(function(){
	$("body").addClass("JS");
	$(".ccp_once").click(function(){
		window.location.href = '<?=$c['baseurl']?>';	
	});
	$(".ccp_once").hover(function(){
		$(this).css("cursor", "pointer");
	},function(){
		$(this).css("cursor", "default");
	});
});
</script>
</body>
</html>