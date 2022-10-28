<?php
    session_start();
    
    if(!isset($_SESSION["isUserLogged"]) || !$_SESSION["isUserLogged"]){
        header("location: index.html");
        exit;
    }
?>