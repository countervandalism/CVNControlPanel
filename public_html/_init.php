<?php
/**
 * head.php ::
 *
 * @package CVN Control Panel
 * @created August 31th, 2010
 * @author Timo Tijhof <timotijhof@gmail.com>, 2010 - 2011
 * 
 * CVN Control Panel by Timo Tijhof is licensed under
 * the Creative Commons Attribution-Share Alike 3.0 Unported License
 * creativecommons.org/licenses/by-sa/3.0/
 */
/**
 *  TODO's, FIXME's and IDEAS
 * -------------------------------------------------
 */
/*
- In the future switch to MySQL. Allows to easily add more information, like who did the command and whether or not it was already executed. With the filesystem, if after 4 minutes no bot shows up. - no body knows what could be going on. In MySQL the cronjob could set a flag as 'done' with timestamp, and remove entries older then 5 days whatever.
*/

/**
 *  Configuration
 * -------------------------------------------------
 */

require_once('../config.php');

// Global variables
$c['pscmd'] = '';
$c['logdir'] = ''; // Has trailing slash
$c['bots'] = array();
$c['title'] = 'Control Panel';
$c['baseurl'] = $c['cvntoolsbaseurl'].'/ControlPanel/';
$c['revID'] = '0.9.0';
$c['revDate'] = '2012-09-03';
$c['inifile'] = $c['tshomepath'] . '/backend/services.ini';

// Redirect seconds interval
$s['redirectsecint'] = 10;

/**
 *  Functions
 * -------------------------------------------------
 */
function loadSettings() {
	global $c;

	$cfg = parse_ini_file($c['inifile'], true);
	if (!isset($cfg['CVNBotsControlPanel']))
		krDie("INI Error:<br/>\nInvalid configuration");
	
	// These are ending in file-extension or with trailing slash !
	$c['ps_dump'] = $cfg['CVNBotsControlPanel']['ps_dump'];
	$c['logdir' ] = $cfg['CVNBotsControlPanel']['logdir'];
	$c['commands_queue'] = $cfg['CVNBotsControlPanel']['commands_queue'];
	
	foreach ($cfg as $heading => $contents) {
		if ($heading != 'CVNBotsControlPanel') {
			$c['bots'][$heading] = array(
				// 0: startcmd
				$contents['startcmd'],
				// 1: ARRAY OF PIDs or FALSE
				FALSE,
				// 2: ARRAY OF STIMEs or FALSE
				FALSE,
				// 3: chdir
				$contents['chdir'],
				// 4: startcmdarg
				$contents['startcmdarg'],
				// 5: disabled
				$contents['disabled'],
				// 6: comment
				$contents['comment'],
				// 7: statusdetectpath
				$contents['statusdetectpath'],
			);
		}
	}
}

function getServicesStatus() {
	global $c;
	
	$fPS = fopen($c['ps_dump'],'r'); // Read the ps_dump
	
	// Avoid infinite loop through while(!feof)
	if($fPS === FALSE){
		return false;
	}
	$c['last_ps_update'] = filemtime($c['ps_dump']);
	flock($fPS, LOCK_EX); // Lock the file exclusively
	
	// Read it line by line
	while (!feof($fPS)) {
		$buffer = fgets($fPS);
		// Sample:
		// UID        PID  PPID  C STIME TTY          TIME CMD
		// cvn      30326     1 22 00:11 pts/22   00:00:00 mono /home/project/c/v/n/cvn/bots/swmtbots/CVNBot14/SWMTBot.exe
		$matches = preg_split( '/\s+/', trim( $buffer ), 8 );
		$map = array(
			0 => 'UID',
			1 => 'PID',
			2 => 'PPID',
			3 => 'C',
			4 => 'STIME',
			5 => 'TTY',
			6 => 'TIME',
			7 => 'CMD',
		);
		$psLine = array();
		foreach ( $map as $i => $key ) {
			$psLine[$key] = $matches[$i];
		}

		foreach ( $c['bots'] as $botname => $botvars) {
			if ( strpos( $psLine['CMD'], $botvars[7] ) !== FALSE ) {
				$c['bots'][$botname][1][] = $psLine['PID'];
				$c['bots'][$botname][2][] = $psLine['STIME'];
			}
		}
	}
	fclose($fPS); // Close and release the file
}

function cvnCpScheduleRefresh($url = null) {
	global $s;
	
	$reloadJS = is_string($url) ? 'window.location.href = ' . json_encode($url) . ';' : 'window.location.reload(true);';
	
	$html = <<<HTML
<p style="font-size: smaller; text-align: center;"><em>This page automatically refreshes every {$s['redirectsecint']} seconds.</em></p>
<script>
window.CVN_refreshTimeout = setTimeout(function refresh(){
	window.location.hash = '';
	$reloadJS
}, {$s['redirectsecint']}000);
</script>
HTML;

	echo $html;
}

function cvnCpGetLastShellSync() {
	global $c;

	$diff = time() - $c['last_ps_update'];

	return 'Last updated: '
		. date('l, j F Y H:i', $c['last_ps_update'])
		. ' <small>(' . $diff . ' seconds ago)</small>';
}

function getQueue(){
	global $c;

	$queue = file_get_contents( $c['commands_queue'] );
	if ( !empty($queue) ) {
		$queue = explode( "\n", $queue );
		echo implode( "\n<br/>", $queue );
	} else {
		echo '<em>Queue is empty.</em>';
	}
}
