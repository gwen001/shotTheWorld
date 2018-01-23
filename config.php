<?php

include( __DIR__.'/Utils.php' );


define( 'HTML_HEADER', __DIR__.'/header.html' ); // html header
define( 'HTML_FOOTER', __DIR__.'/footer.html' ); // html footer
define( 'HTML_SERVICE', __DIR__.'/service.html' ); // html footer

define( 'MAX_ITEM_PER_PAGE', 10 ); // the name says everything
define( 'OUTPUT_DIR', __DIR__.'/output/' ); // output file
define( 'OUTPUT_FILE', OUTPUT_DIR.'shottheworld-__PAGE__.html' ); // output file

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
	$str = trim( $str );
	
	if( !strlen($str) ) {
		return 'empty';
	}
	if( strstr($str,'FTP') || strstr($str,'220-') || strstr($str,'220 ftp server') || strstr($str,'sftp') || strstr($str,'ActiveTransfer Ready') ) {
		return 'FTP';
	}
	if( strstr($str,'HTTP/1.') || stristr($str,'<html>') || stristr($str,'<!DOCTYPE HTML PUBLIC') || stristr($str,'<?xml version="1.0"?>') ) {
		return 'HTTP';
	}
	if( strstr($str,'SSH') ) {
		return 'SSH';
	}
	if( strstr($str,'MySQL') ) {
		return 'MYSQL';
	}
	if( strstr($str,'POP3') || strstr($str,'IMAP4rev1') || strstr($str,'+OK Dovecot ready') ) {
		return 'Mail';
	}
	if( strstr($str,'ESMTP Postfix') || strstr($str,'Microsoft ESMTP') ) {
		return 'Mail';
	}
	if( strstr($str,'Connection timed out') ) {
		return 'timeout';
	}
	if( strstr($str,'Connection refused') ) {
		return 'refused';
	}
	if( strstr($str,'No route to host') ) {
		return 'failed';
	}
	
	return 'unknown';
}


/**
 * render result
 *
 * @param string $tpl
 * @param array $ip
 * @param integer $page
 */
function render( $tpl, $page, $args=array(), $htmlencode=true )
{
	$html = file_get_contents( $tpl );
	
	foreach( $args as $k=>$v ) {
		$k = '__'.strtoupper($k).'__';
		if( $htmlencode ) {
			$v = htmlspecialchars( $v );
		}
		$v = str_replace( "\n", "<br/>\n", $v );
		$html = str_replace( $k, $v, $html );
	}
	
	return file_put_contents( str_replace('__PAGE__',$page,OUTPUT_FILE), $html, FILE_APPEND );
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


/**
 * socket function
 *
 * @param string $ip
 * @param integer $port
 * @param integer $page
 */
function connect( $ip, $port, $page )
{
	$output = '';
	echo "calling ".$ip." ".$port."\n";

	ob_start();
	$fp = @fsockopen( $ip, $port, $errno, $errstr, 3 );
	echo "Trying to connect to ".$ip." on port ".$port."\n\n";
	
	if( $fp )
	{
		echo "Connection opened ".$ip." ".$port."\n\n";
		fwrite( $fp, SEND_STRING );
	    stream_set_timeout( $fp, DELAY_SOCKET );
	    $output .= fread( $fp, RESULT_LENGTH );
	    $info = stream_get_meta_data( $fp );
	    fclose($fp);
	
	    if( $info['timed_out'] ) {
	        $output .= "\nConnection timed out!\n";
	    }
	    
		$t_result[$ip][$port] = $output;
	    echo $output."\n";
	}
	else
	{
		$output = $errstr;
		echo $errstr."\n";
	}
	
	$display = ob_get_contents();
	ob_end_clean();

	$service = interp( $output );
	//echo $service."\n";
	$args = [ 'ip'=>$ip, 'port'=>$port, 'service'=>$service, 'output'=>$display ];
	render( HTML_SERVICE, $page, $args );
}

?>