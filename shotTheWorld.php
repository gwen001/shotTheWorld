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



function run( $ip, $port )
{
	echo "-> called ".$ip." ".$port."\n";
	$wtitle = createWindowTitle( $ip, $port );
	
	$cmd = createConnectCommand( $ip, $port );
	$term = createTerminalCommand( $cmd, $wtitle );
	system( $term );
}


posix_setsid();
declare( ticks=1 );
pcntl_signal( SIGCHLD, 'signal_handler' );

$n_child = 0;
$t_process = [];
$t_signal_queue = [];


file_put_contents( OUTPUT_FILE, $html_header );

$t_origin = $t_input = file( $input_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
$run = true;
$n_loop = 1;

do
{
	//var_dump($t_input);
	$cnt = count( $t_input );
	echo "\n".str_pad( ' LOOP '.$n_loop.' ('.$cnt.' ip) ', 50, '#', STR_PAD_BOTH )."\n\n";
	
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
				run( $ip, $port );
				exit( 0 );
			}
		}
	
		killWindow( WINDOW_TITLE_PREFIX, MAX_WINDOW );
		
		usleep( MT_SLEEP );
	}
	
	while( $n_child ) {
		echo $n_child." childs remaining!\n";
		// surely leave the loop please :)
		sleep( 1 );
	}
	
	echo "Killing extra window...\n";
	sleep( DELAY_THEEND );
	killWindow( WINDOW_TITLE_PREFIX );
	
	echo $cnt." ips provided.\n";
	$t_shot = glob( OUTPUT_DIR.'*.png' );
	//var_dump($t_shot);
	echo count($t_shot)." screenshots found.\n";
	$t_shot = array_map( function($v){return str_replace('.png','',str_replace('_',':',basename($v)));}, $t_shot );
	//var_dump( $t_shot );
	//exit();
	$t_diff = array_values( array_diff($t_origin, $t_shot) );
	echo count($t_diff)." ips to retry!\n";
	//var_dump( $t_diff );
	
	if( count($t_diff) && $n_loop<MAX_LOOP ) {
		$n_loop++;
		$t_input = $t_diff;
	} else {
		$run = false;
	}
}
while( $run );


file_put_contents( OUTPUT_FILE, $html_footer, FILE_APPEND );

exit();

?>