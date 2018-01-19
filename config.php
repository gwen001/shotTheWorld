<?php

include( __DIR__.'/Utils.php' );


define( 'HTML_HEADER', __DIR__.'/header.html' ); // html header
define( 'HTML_FOOTER', __DIR__.'/footer.html' ); // html footer
define( 'HTML_TEMPLATE', __DIR__.'/template.html' ); // html footer

define( 'OUTPUT_DIR', __DIR__.'/output/' ); // output file
define( 'OUTPUT_FILE', OUTPUT_DIR.'shottheworld.html' ); // output file

if( !is_dir(OUTPUT_DIR) ) {
	@mkdir( OUTPUT_DIR, 0777, true );
}
if( !is_dir(OUTPUT_DIR) || !is_writable(OUTPUT_DIR) ) {
	exit( 'Error: cannot write in '.OUTPUT_DIR."!\n" );
}

define( 'MT_MAX_CHILD', 15 ); // n threads
define( 'MT_SLEEP', 100000 ); // 0.1 scd

define( 'DELAY_SOCKET', 2 ); // before timeout
define( 'SEND_STRING', 'GET / HTTP/1.1\n' ); // string to send
define( 'RESULT_LENGTH', 1000 ); // length to crop the result



/**
 * Interpreter
 *
 * @param string $str
 * @return string
 */
function interp( $str )
{
	if( !strlen($str) ) {
		return 'empty';
	}
	if( strstr($str,'HTTP') ) {
		return 'HTTP';
	}
	if( strstr($str,'SSH') ) {
		return 'SSH';
	}
	if( strstr($str,'MySQL') ) {
		return 'MYSQL';
	}
	if( strstr($str,'POP3') ) {
		return 'POP3';
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
 * render result
 *
 * @param array $ip
 */
function render( $args )
{
	$html = file_get_contents( HTML_TEMPLATE );
	
	foreach( $args as $k=>$v ) {
		$k = '__'.strtoupper($k).'__';
		$v = htmlspecialchars( $v );
		$v = str_replace( "\n", "<br/>\n", $v );
		$html = str_replace( $k, $v, $html );
	}
	
	file_put_contents( OUTPUT_FILE, $html, FILE_APPEND );
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

?>