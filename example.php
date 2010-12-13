<?php
require_once('activeRecord.php');

//THIS IS A SIMPLE EXAMPLE OF EAR

class user extends activeRecord{}

$u = new user;
$u->email = "anEmail@anEmail.info";
$u->save();

//THAT's IT.  If you have a table names user with id column, properly configured, and column names user that can store strings, you are done.  

