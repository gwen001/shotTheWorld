<h1 align="center">shotTheWorld</h1>

<h4 align="center">PHP tool that takes screenshots of a given ips/ports combo list and then try to guess the service.</h4>

<p align="center">
    <img src="https://img.shields.io/badge/php-%3E=7.2.5-blue" alt="php badge">
    <img src="https://img.shields.io/badge/license-MIT-green" alt="MIT license badge">
    <a href="https://twitter.com/intent/tweet?text=https%3a%2f%2fgithub.com%2fgwen001%2fshotTheWorld%2f" target="_blank"><img src="https://img.shields.io/twitter/url?style=social&url=https%3A%2F%2Fgithub.com%2Fgwen001%2FshotTheWorld" alt="twitter badge"></a>
</p>

<p align="center">
    <img src="https://img.shields.io/github/stars/gwen001/shotTheWorld?style=social" alt="github stars badge">
    <img src="https://img.shields.io/github/watchers/gwen001/shotTheWorld?style=social" alt="github watchers badge">
    <img src="https://img.shields.io/github/forks/gwen001/shotTheWorld?style=social" alt="github forks badge">
</p>

---

## Description

shotTheWorld uses a different approach to determine what service if behind an opened port.
It takes a text screenshot of a socket connection and render the output in a HTML file located in the output directory.  

## Install

```
git clone https://github.com/gwen001/shotTheWorld
```

## Usage 

```
Usage: php shotTheWorld.php <combo list>

Options:
	check config.php to manually change the options

Examples:
	php shotTheWorld.php combos.txt
```

The source file should respect the following format:  
```
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
xxx.xxx.xxx.xxx:port
...
```

---

<img src="https://raw.githubusercontent.com/gwen001/shotTheWorld/main/preview.png" alt="shotTheWorld preview">

---

Feel free to [open an issue](/../../issues/) if you have any problem with the script.  
