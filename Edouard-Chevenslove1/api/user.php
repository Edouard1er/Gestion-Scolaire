<?php 

    require "../session/connection.php";
    require "../function-php/function.php";
    require "../utils/put_method_delete.php";
    require "../utils/start.php";
    $request_method = $_SERVER['REQUEST_METHOD'];
    // $userId=$_SESSION["user"]["userId"];
    switch($request_method)
    {
        case 'GET':
            if(isset($_GET["userId"]) && !empty($_GET["userId"]))
                readUser(intval($_GET["userId"]));
            else
                readUser();
            break;
        case 'POST':
            createUser();
            break;
        case 'PUT':
            updateUser();
            break; 
        case 'DELETE':
            deleteUser();
            break;
        default:
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }
    

    function createUser(){
        global $pdo;

        $response= array();
        $username   = isset($_POST["username"])?$_POST["username"]:"";
        $password         = isset($_POST["password"])?$_POST["password"]:"";
        $confirm_password   = isset($_POST["confirm_password"])?$_POST["confirm_password"]:"";
        $role       = isset($_POST["role"])?$_POST["role"]:"ELEVE";
        $username_err = $password_err = $confirm_password_err = "";
        
        if(empty(trim($username))){
            $username_err = "SVP entrer l'utilisateur.";
            $response=throwError($username_err,-1);
        } else if(!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))){
            $username_err = "Le nom d'utilisateur ne peut contenir que des lettres, des chiffres et des caractères de soulignemen.";
            $response=throwError($username_err,-2);
        }else{
            $sql = "SELECT userId FROM users WHERE username = :username AND statut='1'";
            
            if($statement = $pdo->prepare($sql)){
                $statement->bindParam(":username", $username, PDO::PARAM_STR);
                                
                if($statement->execute()){
                    if($statement->rowCount() > 0){
                        $username_err = "Username already exists.";
                        $response=throwError($username_err, -3);
                    } else{
                        $username = trim($_POST["username"]);
                    }
                } 
                unset($statement);
            }
            if(empty($username_err)){
                if(empty(trim($_POST["password"]))){
                    $password_err = "Please enter a password."; 
                    $response=throwError($password_err,-4);    
                } else if(strlen(trim($_POST["password"])) < 6){
                    $password_err = "Le mot de passe doit comporter au moins 6 caractères.";
                    $response=throwError($password_err,-5);  
                } else {
                    $password = trim($_POST["password"]);
                    if(empty(trim($confirm_password))){
                        $confirm_password_err = "Confirmez votre mot de passe."; 
                        $response=throwError($confirm_password_err,-6);    
                    } else{
                        $confirm_password = trim($confirm_password);
                        if(empty($password_err) && ($password != $confirm_password)){
                            $confirm_password_err = "Les mots de passe sont differents.";
                            $response=throwError($confirm_password_err,-7);
                        }
                    }
                } 
            }
        } 
        
        if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
            try {
                $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
                if($statement = $pdo->prepare($sql)){
                    $statement->bindParam(":username", $username, PDO::PARAM_STR);
                    $statement->bindParam(":password", $password, PDO::PARAM_STR);
                    $statement->bindParam(":role", $role, PDO::PARAM_STR);
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    if($statement->execute()){
                        $response = throwSuccess();
                        $response["data"]["userId"]=$pdo->lastInsertId();
                    } 
                    unset($statement);
                }
            } catch (PDOException $exception) {
                if($exception->errorInfo[0]=="23000"){
                    $response=throwError("l'utilisateur existe deja !",$exception->errorInfo[0]);
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);

        unset($pdo);
    }

    function readUser($userId=""){
        global $pdo;

        $response=array();
        try {
            $sql = "SELECT u.userId,u.username, u.first_name, u.last_name,r.libelle AS role_libelle, u.role as role_code, u.picture, u.last_login, u.created_at 
            FROM users AS u
            INNER JOIN role AS r ON (r.code=u.role)
            WHERE statut='1'";
        if(!empty(withoutSpace($userId))){
            $sql .= " AND userId=?";
        }


        if($statement = $pdo->prepare($sql)){
            if(!empty(withoutSpace($userId))){
                $statement->execute([$userId]);
            }else{
                $statement->execute();
            }
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

    function updateUser(){
        global $_PUT;
        $id = isset($_PUT["userId"])?intval($_PUT["userId"]):(isset($_GET["userId"])?intval($_GET["userId"]):"");
        $response=updateTable("users",$id, $_PUT,"userId");
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function deleteUser(){
        global $_DELETE;
        $id = isset($_DELETE["userId"])?intval($_DELETE["userId"]):(isset($_GET["userId"])?intval($_GET["userId"]):"");
        $response=deleteRowProv("users",$id,"userId");
        header('Content-Type: application/json');
        echo json_encode($response);
    }
?>