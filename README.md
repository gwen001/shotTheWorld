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
```
PHP functions pcntl* enabled  
xfce4-terminal  
xwd  
```

Test:
```
674 port screenshoted in 2mn23 with 15 threads, 2 pass
3398 port screenshoted in 14mn01 with 25 threads, 3 pass
```

# UPDATE
Code review  
No image anymore  
jQuery and Bootstrap implemented  
Menu to select what items to display  
Speed improvement (test: 3398 port scanned in 4mn46 with 15 threads)  


<br><br>
<img src="https://raw.githubusercontent.com/gwen001/shotTheWorld/master/example.png" alt="shotTheWorld">
<br><br>


I don't believe in license.  
You can do want you want with this program.  
