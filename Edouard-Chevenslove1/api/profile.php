<?php 

    require "../session/connection.php";
    require "../function-php/function.php";
    require "../utils/put_method_delete.php";
    require "../utils/start.php";
    $request_method = $_SERVER['REQUEST_METHOD'];
    $userId=$_SESSION["user"]["userId"];

    if($request_method=="PUT"){
        updateProfile();        
    }else if($request_method=="GET"){
        readProfile();
    }else{
        header("HTTP/1.0 405 Method Not Allowed");
    }

    function updateProfile(){
        global $_PUT, $userId;
        $where="userId='".$userId."'";
        $exceptionField=array("username", "password","role", "statut","picture");
        if(array_key_exists("birthday",$_PUT)) $_PUT["birthday"]=convertToDbDate($_PUT["birthday"]);
        $response=updateTable("users",$userId, $_PUT,"userId",$where,0,$exceptionField);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function readProfile(){
        global $pdo, $userId;

        $response=array();
        try {
            $sql = "SELECT u.userId,u.username, u.first_name, u.last_name,
            r.libelle AS role_libelle, u.role as role_code,u.telephone,
            u.email,u.gender,u.home_address,u.birthday 
            FROM users AS u
            INNER JOIN role AS r ON (r.code=u.role)
            WHERE statut='1' AND userId=?";
        


        if($statement = $pdo->prepare($sql)){
            $statement->execute([$userId]);
            if($statement->rowCount() > 0){
                $response=throwSuccess();
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $response["data"][]=$row;
                }
            }
        }
        unset($statement);
        } catch(PDOException $exception){ 
            $response=throwError($exception->errorInfo?$exception->errorInfo:"Error!");   
        }
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);

        unset($pdo);
    }
?>