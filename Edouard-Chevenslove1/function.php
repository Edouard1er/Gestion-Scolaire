<?php
    function inputFilter($myString){
        $myString=htmlentities(trim($myString));
        return $myString;
    }
?>