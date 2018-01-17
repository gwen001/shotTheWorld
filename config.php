<?php

include( __DIR__.'/Utils.php' );


define( 'MAX_LOOP', 5 ); // max retry if something failed
define( 'MAX_WINDOW', 100 ); // max window opened
define( 'DELAY_THEEND', 5 ); // secondes before killing all opened window

define( 'OUTPUT_DIR', __DIR__.'/output/' ); // output file
define( 'OUTPUT_FILE', OUTPUT_DIR.'shottheworld.html' ); // output file

if( !is_dir(OUTPUT_DIR) ) {
	@mkdir( OUTPUT_DIR, 0777, true );
}
if( !is_dir(OUTPUT_DIR) || !is_writable(OUTPUT_DIR) ) {
	exit( 'Error: cannot write in '.OUTPUT_DIR."!\n" );
}

define( 'MT_MAX_CHILD', 25 ); // n threads
define( 'MT_SLEEP', 100000 ); // 0.1 scd

define( 'WINDOW_TITLE_PREFIX', 'shotTheWorld >' );

define( 'RESULT_LENGTH', 500 ); // length to crop the result
define( 'DELAY_SOCKET', 1 ); // before timeout
define( 'DELAY_SHOT', 2000000 ); // before taking the screenshot
define( 'SEND_STRING', 'GET / HTTP/1.1\n' ); // string to send



function createWindowTitle( $ip, $port )
{
	return WINDOW_TITLE_PREFIX.' '.$ip.':'.$port;
}


function createImageName( $ip, $port, $full_path=true )
{
	$img = $ip.'_'.$port.'.png';
	
	if( $full_path ) {
		$img = OUTPUT_DIR.$img;
	}
	
	return $img;
}


function createConnectCommand( $ip, $port )
{
	return __DIR__.'/connect.php '.$ip.' '.$port;
}


function createTerminalCommand( $cmd, $wtitle )
{
	return 'xfce4-terminal --hide-toolbar --hide-menubar --geometry 100x30+0+0 -H --command "'.$cmd.'" --title "'.$wtitle.'"';
}


function createShotCommand( $wtitle, $img )
{
	//return 'shutter -C --window=.*'.$wtitle.'.* -o "'.$img.'" 2>/dev/null';
	return 'xwd -silent -name "'.$wtitle.'" | convert xwd:- '.$img;
}


/**
 * Interpreter
 *
 * @param string $str
 * @return string
 */
function interp( $str )
{
	if( strstr($str,'HTTP') ) {
		return 'HTTP';
	}
	if( strstr($str,'SSH') ) {
		return 'SSH';
	}
	if( strstr($str,'MySQL') ) {
		return 'MYSQL';
	}
	if( strstr($str,'Connection timed out') ) {
		return 'timeout';
	}
	if( strstr($str,'Connection refused') ) {
		return 'refused';
	}
	
	return 'unknown';
}


/**
 * Window killer
 *
 * @param string $str
 * @param integer $max_window
 * @return integer
 */
function killWindow( $str, $max_window=0 )
{
	exec( "wmctrl -l | grep '".$str."' | awk '{print $1}'", $t_window );
	//var_dump( $t_window );
	$n = count( $t_window );
	if( $max_window > $n ) {
		return 0;
	}

	$to = $n - $max_window;
	
	for( $i=0 ; $i<$to ; $i++ ) {
		$win_id = $t_window[$i];
		echo '   '.$win_id."\n";
		if( strstr($win_id,'0x0') ) {
			exec( 'wmctrl -ic '.$win_id );
		}
	}
	
	echo $to." window killed!\n";
	return $to;
}


// http://stackoverflow.com/questions/16238510/pcntl-fork-results-in-defunct-parent-process
// Thousand Thanks!
function signal_handler( $signal, $pid=null, $status=null )
{
	global $t_process, $n_child, $t_signal_queue;
	
	// If no pid is provided, Let's wait to figure out which child process ended
	$pid = (int)$pid;
	if( !$pid ){
		$pid = pcntl_waitpid( -1, $status, WNOHANG );
	}
	
	// Get all exited children
	while( $pid > 0 )
	{
		if( $pid && isset($t_process[$pid]) ) {
			// I don't care about exit status right now.
			//  $exitCode = pcntl_wexitstatus($status);
			//  if($exitCode != 0){
			//      echo "$pid exited with status ".$exitCode."\n";
			//  }
			// Process is finished, so remove it from the list.
			$n_child--;
			unset( $t_process[$pid] );
		}
		elseif( $pid ) {
			// Job finished before the parent process could record it as launched.
			// Store it to handle when the parent process is ready
			$t_signal_queue[$pid] = $status;
		}
		
		$pid = pcntl_waitpid( -1, $status, WNOHANG );
	}
	
	return true;
}



$html_header = '<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html class="no-js lt-ie9 lt-ie8" lang="en"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html class="no-js lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"><!--<![endif]-->
<head>
<meta charset="utf-8">
<title>Report</title>
<link rel="stylesheet" href="style.css">
</head>
<body>';


$html_footer = "</body>
</html>";


?>