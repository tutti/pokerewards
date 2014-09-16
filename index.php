<?php

session_start();
error_reporting(E_ALL);

require_once "Classes/User.php";
$user = User::from_session();

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Pokémon rewards!</title>
    </head>
    <body>
        <?php
        
        $action = "default";
        if (array_key_exists('a', $_GET)) {
            $action = $_GET['a'];
        }
        
        if (is_file("actions/$action.php")) {
            require "actions/$action.php";
        } else {
            require "actions/404.php";
        }
        
        ?>
    </body>
</html>

