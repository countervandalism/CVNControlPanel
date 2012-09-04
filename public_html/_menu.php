<?php

$anchorTags = array();
foreach( array(
	'Index' => $c['baseurl'],
	'Bot output' => 'console_output.php',
	'Pending commands' => 'pending.php',
	'Control log' => 'control_log.php',
	) as $text => $location ) {
	
	$anchorTags[] = '<a href="' . htmlspecialchars( $location ) . '">' . htmlspecialchars( $text ) . '</a>';
	
}
?>
		<p style="float: right;"><small>Page generated <?php echo date($c['fulldatefmt']) ?></small> <b>&nbsp;&bull;&nbsp;</b> Welcome back, <?=ucfirst(krUserLink())?></p>
		<p style="float: left;"><?php echo implode( ' <b> &bull; </b> ', $anchorTags ); ?></p>
