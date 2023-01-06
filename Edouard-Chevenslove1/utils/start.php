<?php
    session_start();
    
    if(!isset($_SESSION["isUserLogged"]) || !$_SESSION["isUserLogged"]){
        header("location: index.html");
        exit;
    }

    if($_SESSION["user"]["first_login"]==1 && $_SERVER["REQUEST_URI"] !="/compte.php" && $_SERVER["REQUEST_URI"] !="/update_password"){
        header("location: compte.php");
        exit;
    }

    

    if($_SESSION["user"]["role"] !="ADMIN" && $_SERVER["REQUEST_URI"] =="/admin.html"){
        header("location: form.html");
        exit;
    }

    if($_SESSION["user"]["role"] !="PROF" && $_SERVER["REQUEST_URI"] =="/eleve.html"){
        header("location: form.html");
        exit;
    }

    if($_SESSION["user"]["role"] =="ADMIN" && $_SERVER["REQUEST_URI"] =="/cours.html"){
        header("location: form.html");
        exit;
    }
?>