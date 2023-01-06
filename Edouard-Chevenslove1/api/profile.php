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
    }else if($request_method=="POST"){
        updatePassword();
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

    function updatePassword(){
        global $pdo;

        $response= array();
        $password         = isset($_POST["password"])?$_POST["password"]:"";
        $confirm_password   = isset($_POST["confirm_password"])?$_POST["confirm_password"]:"";
        $password_err = $confirm_password_err = "";
        $username   = $_SESSION["user"]["username"];
       
            
            if(!empty($username)){
                if(empty(trim($_POST["password"]))){
                    $password_err = "Please enter a password."; 
                    $response=throwError($password_err,-4);    
                } else if(strlen(trim($_POST["password"])) < 6){
                    $password_err = "Password should have at least 6 characters.";
                    $response=throwError($password_err,-5);  
                } else {
                    $password = trim($_POST["password"]);
                    if(empty(trim($confirm_password))){
                        $confirm_password_err = "Please confirm password."; 
                        $response=throwError($confirm_password_err,-6);    
                    } else{
                        $confirm_password = trim($confirm_password);
                        if(empty($password_err) && ($password != $confirm_password)){
                            $confirm_password_err = "Password did not match.";
                            $response=throwError($confirm_password_err,-7);
                        }
                    }
                } 
            }
        
        
        if(empty($password_err) && empty($confirm_password_err)){
            try {
                $sql="SELECT password FROM users WHERE  username='".$username."' AND statut=1";
                $ancien_password="";
                if($statement = $pdo->prepare($sql)){
                    $statement->execute();
                    if($statement->rowCount() > 0){
                        $response=throwSuccess();
                        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                            $ancien_password=$row["password"];
                        }
                    }
                }

                
                if(password_verify($password, $ancien_password)){
                    $response=throwError(-2);
                }else{
                    $sql = "UPDATE users SET password= :password, first_login=0 WHERE username='".$username."'";
                    if($statement = $pdo->prepare($sql)){
                        $statement->bindParam(":password", $password, PDO::PARAM_STR);
                        $password = password_hash($password, PASSWORD_DEFAULT);
                        if($statement->execute()){
                            $response = throwSuccess();
                            $_SESSION["user"]["first_login"]=0;
                        } 
                        unset($statement);
                    }
                }
            } catch (PDOException $exception) {
                
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);

        unset($pdo);
    }
?>