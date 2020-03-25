## watcher.php
用于监控文件变化,当文件发生变化以后可以执行后续操作，目前自学C/C++，每次gcc xx.c && ./a.out,比较烦，随手写了这么个工具
`php dev.php --watch=/root/c/pointer,/root/c/scope -t=1`
意思是监听`/root/c/pointer`,`/root/c/scope`这两个目录,每隔一秒扫描一次,看是否有文件发生了更新