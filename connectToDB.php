<?php
// This file is to allow the files in public to access the mysql server
// only files in source can have this code for security reasons

// connects to the SQL server

// host, username, password, dbname
// Specifies a host name or an IP address
// Specifies the MySQL username
// Specifies the MySQL password
// Specifies the default database to be used
$conn = mysqli_connect('hs-quad-prd-mysql01.private.mysql.database.azure.com', 'quadadmin', 'J1f9G5AH5gG62oWTfHM2D9Snqz415p', 'test');

// Checks if the connection is good
if(!$conn){
    echo 'Connection error' . mysqli_connect_error();
}
?>