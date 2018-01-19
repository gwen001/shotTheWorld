#!/usr/bin/php
<?php

include( __DIR__.'/config.php' );



function usage( $err=null ) {
  echo 'Usage: '.$_SERVER['argv'][0]." <source file>\n";
  if( $err ) {
    echo 'Error: '.$err."!\n";
  }
  exit();
}

if( $_SERVER['argc'] != 2 ) {
  usage();
}


$input_file = $_SERVER['argv'][1];
if( !is_file($input_file) ) {
	usage( 'source file not found' );
}



function connect( $ip, $port )
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
		echo $errstr."\n";
	}
	
	$output = ob_get_contents();
	ob_end_clean();

	$service = interp( $output );
	//echo $service."\n";
	$args = [ 'ip'=>$ip, 'port'=>$port, 'service'=>$service, 'output'=>$output ];
	render( $args );
}


posix_setsid();
declare( ticks=1 );
pcntl_signal( SIGCHLD, 'signal_handler' );

$n_child = 0;
$t_process = [];
$t_signal_queue = [];


file_put_contents( OUTPUT_FILE, file_get_contents(HTML_HEADER) );

$t_input = file( $input_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
$run = true;
$n_loop = 1;

//var_dump($t_input);
$cnt = count( $t_input );
echo "\n".$cnt." IPs loaded\n\n";

for( $index=0 ; $index<$cnt ; )
{
	list( $ip, $port ) = explode( ':', $t_input[$index] );
	
	if( $n_child < MT_MAX_CHILD )
	{
		$pid = pcntl_fork();
		
		if( $pid == -1 ) {
			// fork error
		} elseif( $pid ) {
			// father
			$n_child++;
			$index++;
			$t_process[$pid] = uniqid();
	        if( isset($t_signal_queue[$pid]) ){
	        	$signal_handler( SIGCHLD, $pid, $t_signal_queue[$pid] );
	        	unset( $t_signal_queue[$pid] );
	        }
		} else {
			// child process
			connect( $ip, $port );
			exit( 0 );
		}
	}
	
	usleep( MT_SLEEP );
}

echo "\n";
while( $n_child ) {
	echo $n_child." childs remaining!\n";
	// surely leave the loop please :)
	sleep( 1 );
}
echo "\n";


file_put_contents( OUTPUT_FILE, file_get_contents(HTML_FOOTER), FILE_APPEND );
copy( 'style.css', OUTPUT_DIR.'style.css' );

exit();

?>