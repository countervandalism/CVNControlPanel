<?php
/**
 * cronjob_cvncp.php :: Periodically updates active processes dump and executes enqueued commands
 *
 * @package CVN Control Panel
 * Created on August 31th, 2010
 *
 * Copyright © 2010 Krinkle <krinklemail@gmail.com>
 * 
 * CVN Control Panel by Krinkle [1] is licensed under
 * a Creative Commons Attribution-Share Alike 3.0 Unported License [2]
 * 
 * [1] <http://commons.wikimedia.org/wiki/User:Krinkle>
 * [2] <http://creativecommons.org/licenses/by-sa/3.0/>
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
error_reporting(-1);
$commands_queue = '/home/project/c/v/n/cvn/backend/commands_queue.txt';
$ps_dump = '/home/project/c/v/n/cvn/backend/ps_dump.txt';
$ps_cmd = '/bin/ps -f -U cvn';
$log = "\n".date('Y-m-d H:i:s').' | ';
echo $log.'Cronjob started';

/**
 *  Read and clear commands queue
 * -------------------------------------------------
 */
$kill_cmds = array();
$start_cmds = array();

// Prepare commands file
$fCmd = fopen($commands_queue, 'r+'); // Open the commands queue
if( $fCmd !== FALSE ){ // Avoid infinite loop through while(!feof)

	echo $log.'Commands file open';
	flock($fCmd, LOCK_EX); // Lock the file exclusively

	// Read it line by line
	while ( !feof($fCmd) ) {
		$buffer = fgets($fCmd);
		if( !empty($buffer) ){
			$cmdParts = explode('|',$buffer);
			switch( $cmdParts[0] ){

			case 'kill' :
				if( !empty($cmdParts[1]) ){
					$kill_cmds[] = trim($cmdParts[1]);
				}
				break;

			case 'start' :
				if( !empty($cmdParts[1]) && !empty($cmdParts[2]) ){
					$start_cmds[] = array('chdir' => trim($cmdParts[1]), 'exec' => trim($cmdParts[2]));
				}
				break;

			default:
				//Empty or invalid line, do nothing
			}
		}
	}
	ftruncate($fCmd,0); // Truncate it to zero
	echo $log.'Commands file lock, read and truncated';
	flock($fCmd, LOCK_UN);
	fclose($fCmd); // Close and unlock the file
	echo $log.'Commands file close/unlock';

} else {
	echo $log.'Could not open commands file';
}

/**
 *  Execute commands
 * -------------------------------------------------
 */
if( !empty($kill_cmds) ){
	
	echo $log.'Executing '.count($kill_cmds). ' kill command(s)';
	foreach($kill_cmds as $kill_cmd){
		exec($kill_cmd);
		sleep(1);
	}

}
if( !empty($start_cmds) ){
	
	if( !empty($kill_cmds) ){
		echo $log.'Found both kill and start commands. Sleeping for 2 seconds now.';
		sleep(2);
	}
	
	echo $log.'Executing '.count($start_cmds). ' start command(s)';
	foreach($start_cmds as $start_cmd){
		chdir($start_cmd['chdir']);
		exec($start_cmd['exec'] . ' &');
		sleep(1);
	}

}


/**
 *  Get PS
 * -------------------------------------------------
 */
if( !empty($kill_cmds) || !empty($start_cmds) ){
	echo $log.'Executed one or more commands, going to get PS. Sleeping first for a second.';
	sleep(1);
}

// Prepare variable
$pResult = '';
// Get PS information
$pPS = popen($ps_cmd,'r');
	
// Avoid infinite loop through while(!feof)
if($pPS === FALSE){
	echo $log.'Could not get PS';
} else {
	// Read it line by line
	while (!feof($pPS)) {
		$pResult .= fgets($pPS);
	}
	pclose($pPS);
	echo $log.'PS command read and closed';
}

/**
 *  Overwrite PS dump
 * -------------------------------------------------
 */

$psWrite = file_put_contents( $ps_dump, $pResult );
if ( $psWrite === false ) {
	echo $log.'ps dump write failed';
}

echo $log.'Cronjob done. Time is: '.date('Y-m-d H:i:s')."\n";
