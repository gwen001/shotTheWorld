<?php

include( __DIR__.'/class.Utils.php' );
include( __DIR__.'/functions.php' );


define( 'HTML_HEADER', __DIR__.'/header.html' ); // html header
define( 'HTML_FOOTER', __DIR__.'/footer.html' ); // html footer
define( 'HTML_SERVICE', __DIR__.'/service.html' ); // html footer

define( 'MAX_ITEM_PER_PAGE', 1500 ); // the name says everything
define( 'OUTPUT_DIR', __DIR__.'/output/' ); // output file
define( 'OUTPUT_FILE', OUTPUT_DIR.'shottheworld-__PAGE__.html' ); // output file

define( 'MT_MAX_CHILD', 15 ); // n threads
define( 'MT_SLEEP', 100000 ); // 0.1 scd

define( 'DELAY_SOCKET', 2 ); // before timeout
define( 'SEND_STRING', "GET / HTTP/1.1\r\n\r\n" ); // string to send
define( 'RESULT_LENGTH', 1024 ); // length to crop the result
