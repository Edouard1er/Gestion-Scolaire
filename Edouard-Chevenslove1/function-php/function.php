<?php

    function inputFilter($myString){
        $myString=htmlentities(trim($myString));
        return $myString;
    }

    function getAmountRow($column, $table, $join, $where){
        global $pdo;
        $return=0;
        $sqlCount = "SELECT count(".$column.") AS count FROM ".$table." ".$join." WHERE ".$where;
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

    function throwSuccess($message="Success", $code=1,$HTTP_STATUS=200){
        http_response_code($HTTP_STATUS); 
        $response=array(
            "message"=>$message,
            "code"=>$code
        );
        return $response;
    }

    function withoutSpace($string){
        $string = preg_replace('/\s+/', '', $string);
        return $string;
    }

    function insertNewUser($name,$email,$password){
        global $pdo;

        $response= array();
        if(!empty(withoutSpace($name)) && !empty(withoutSpace($email)) && !empty(withoutSpace($password))){
            try {
                $password = hash('sha256', $password);
                $sql = "INSERT INTO user (name, email, password) VALUES (:name, :email, :password)";
    
                if($stmt = $pdo->prepare($sql)){
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->bindParam(":password",$password , PDO::PARAM_STR);
                    if($stmt->execute()){
                        $response=throwSuccess();
                        $response["data"]["id"]=$pdo->lastInsertId();
                    }
                    unset($stmt);
                }
            } catch(PDOException $exception){ 
                $response=throwError($exception->errorInfo?$exception->errorInfo:"Erreur!.");   
            }
        }else{
            $response=throwError('ERREUR!. email, name, password sont obligatoire');
        }

        return $response;
    }

    function updateTable($table,$id, $data,$table_id_name="id", $where="", $delete=0){
        global $pdo;

        $response=array();

        if(!empty(withoutSpace($table)) && !empty(withoutSpace($id))){
            if(empty(withoutSpace($where))) $where = $table_id_name."='".$id."'";
            if($table=="users" && array_key_exists("password",$data)) unset($data["password"]);
            if($table=="users" && array_key_exists("confirm_password",$data)) unset($data["confirm_password"]);
            
            $query="UPDATE ${table} SET ";
            $data_length= count($data);
            $compteur=0;
            foreach ($data as $key => $value) {
                $compteur++;
                $query .= "${key} = '".htmlentities($value)."'";
                if($compteur < $data_length)  $query .= " , ";
            }
            $query .= " WHERE ".$where;
            try {
                $stmt= $pdo->prepare($query);
                if($stmt->execute()){
                    $response=throwSuccess();
                    if($delete==1) unset($data["statut"]);
                    $response["data"]=$data;
                }
                
            } catch (PDOException $exception) {
                $message="Error";
                if($exception->errorInfo[0]=="23000") $message="DUPLICATE";
                $response=throwError($message,$exception->errorInfo[0]); 
            }
        }else{
            $response=throwError();
        }
      return $response;
    }

    function deleteRowProv($table, $id,$table_id_name){
        $data=array("statut"=>0);
        return updateTable($table,$id, $data,$table_id_name,"", 1);
    }

    function deleteRowPermanent($table, $id, $table_id_name){
        global $pdo;

        $response = array();
        if(!empty(withoutSpace($table)) && !empty(withoutSpace($id))){
            try {
                $sql = "DELETE FROM ${table}  WHERE ${table_id_name}=?";
                $statement= $pdo->prepare($sql);
                if($statement->execute([$id])){
                    $response=throwSuccess();
                }
            } catch (PDOException $exception) {
                $exception;
                $response=throwError(); 
            }
        }else{
            $response=throwError();
        }

        return $response;
    }

    function insertRow($table,$data){
        global $pdo;

        $response= array();
        if(isEmptyFieldData($data) && !empty($table)){
            try {
                $sql = "INSERT INTO ${table} ";
                $fieldNames=" (";
                $fieldValue=" VALUES (";
                $compt=0;
                $dataLength= count($data);
                $executeArray=array();
                foreach ($data as $key => $value) {
                    $compt ++;
                    $fieldNames .= $key;
                    $fieldValue .= "?";
                    if($compt < $dataLength){
                        $fieldNames .= ",";
                        $fieldValue .= ",";
                    }
                    array_push($executeArray, $value);
                }
                $fieldNames .= ")";
                $fieldValue .= ")";
                $sql .= $fieldNames .  $fieldValue;
    
                if($stmt = $pdo->prepare($sql)){
                    if($stmt->execute($executeArray)){
                        $response=throwSuccess();
                        $response["data"]["id"]=$pdo->lastInsertId();
                    }
                    unset($stmt);
                }
            } catch(PDOException $exception){ 
                $response=throwError($exception->errorInfo?$exception->errorInfo:"Error!");   
            }
        }else{
            $response=throwError();
        }
        return $response;
    }

    function isEmptyFieldData ($array){
        $response=true;
        foreach ($array as $value) {
            if(empty(withoutSpace($value))){
                $response = false;
                break;
            }
        }
        return $response;
    }
?>