# shotTheWorld
PHP tool that take screenshot of a socket connection on a given ip/port list and then try to guess the service.  
The output is a HTML file located in the output directory with all screenshots.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php shotTheWorld.php <source file>

Options:
	check config.php to manually change the options

Examples:
	php shotTheWorld.php test
```

The source file should respect the following format:  
```
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
```

Requirements:

PHP functions pcntl* enabled  
xfce4-terminal  
xwd  


<img src="https://raw.githubusercontent.com/gwen001/shotTheWorld/master/example.png" alt="shotTheWorld">
<br><br>


I don't believe in license.  
You can do want you want with this program.  
