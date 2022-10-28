<?php

    function inputFilter($myString){
        $myString=htmlentities(trim($myString));
        return $myString;
    }

    function getAmountRow($column, $table){
        global $pdo;
        $return=0;
        $sqlCount = "SELECT count(".$column.") AS count FROM ".$table." WHERE statut=1";
        $count=0;
        if($stmtCount = $pdo->prepare($sqlCount)){
            $stmtCount->execute();
            if($stmtCount->rowCount() == 1){
                if($row = $stmtCount->fetch()){
                    $return = $row["count"];
                }
            }
        }
        return $return;
    }

    function toDateAndTime($datetime)
    {
        $return=[];
        if($datetime){
            $datetime= explode(" ", $datetime);
            $return[0]=$datetime[0];
            if($datetime[1]){
                $datetime=explode(":",$datetime[1]);
                $return[1]=$datetime[0].":".$datetime[1];
            }
        }
        return $return;
    }

    function verifyPostValue($value)
    {
        if(!isset($_POST[$value]) || empty($_POST[$value]))
            return false;
        return true;
    }

    function throwError($message="Error", $code=0,$HTTP_STATUS=400){
        http_response_code($HTTP_STATUS); 
        $response=array(
            "message"=>$message,
            "code"=>$code
        );
        return $response;
    }
?>