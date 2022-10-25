<?php

require 'classes/Dbh.php';
require 'classes/UserAuth.php';
require 'classes/Route.php';

$route = new FormController();

$route->handleForm();

//<?php echo ( isset( $_POST['name'] ) ? htmlspecialchars($_POST['name']) : '' ); 
?>