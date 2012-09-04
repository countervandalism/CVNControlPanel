<?php
/**
 * console_output.php
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

loadSettings();

?>
<!DOCTYPE html> 
<html dir="ltr" lang="en-US"> 
<head> 

	<meta charset="utf-8"> 

	<title>CVN | <?=$c['title']?> - View log</title>

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
		
<?php
if ( !$s['bot'] ){

	$optionsHtml = '';
	foreach( $c['bots'] as $botName => $botVars ) {
		$optionsHtml .= '<option value="' . htmlspecialchars( $botName ) . '">' . htmlspecialchars( $botName ) . '</option>';
	}
	echo '
		<h2>Console output</h2>';
	krMsg('
		<p>Please select a bot ID:</p>
		<form>
			<select name="bot">' . $optionsHtml . '</select>
			<input type="submit"/>
		</form>
	');

} else {
?>
		
		<h2>Console output for: <?=$s['bot']?></h2>
		<pre class="wikitable"><?php
			echo krEscapeHTML( file_get_contents( $c['logdir']. $s['bot'] . '.log' ) );
		?></pre>

<?php
}
?>
		<hr id="footer"/>
		<address><?=$c['title']?> <?=$c['revID']?> (<?=$c['revDate']?>) on <?php echo $_SERVER['HTTP_HOST'];?></address>
</div>
</body>
</html>
