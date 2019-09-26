<?php
	error_reporting(0);
	
	$host="localhost";
	$uname="root";
	$pass="root123";
	//$database = "frontier_test_db";
	$database = "nfd_equo";	
	
	$connection=mysql_connect($host,$uname,$pass) or die("Database Connection Failed");
	$selectdb=mysql_select_db($database) or die("Database could not be selected");	

	$result=mysql_select_db($database)
	or die("database cannot be selected <br>");
	session_start();
	set_time_limit(0);

?>
