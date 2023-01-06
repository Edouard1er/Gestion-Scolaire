<?php
    session_start();
    
    require_once ("../session/connection.php");
    require_once ("../function-php/function.php");
    
    $username = $password = $usernameError = $passwordError = $loginErrow = "";
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST["request"]) && isset($_SESSION["isUserLogged"]) && $_SESSION["isUserLogged"] === true){
            $response=throwSuccess();
            $response["data"]= $_SESSION["user"]; 
        }else{
            if(empty(inputFilter($_POST["username"]))){
                $usernameError = "Veuillez entrer un nom d'utilisateur.";
                $response=throwError($usernameError,-1,200);
            } else{
                $username = inputFilter($_POST["username"]);
            }
            
            if(empty($usernameError) && empty(inputFilter($_POST["password"]))){
                $passwordError = "Veuillez entrer un mot de passe.";
                $response=throwError($passwordError,-2,200);
            } else{
                $password = inputFilter($_POST["password"]);
            }
            
            if(empty($usernameError) && empty($passwordError)){
                $loginError = "Nom d'utilisateur/mot de passe incorrect.";
                $sql = "SELECT userId, username,name,picture, password,role,first_login FROM users WHERE username = :username AND statut=1";
                
                if($statement = $pdo->prepare($sql)){
                    $statement->bindParam(":username", $paramUsername, PDO::PARAM_STR);
                    
                    $paramUsername = trim($_POST["username"]);
                    
                    if($statement->execute()){
                        if($statement->rowCount() == 1){
                            if($row = $statement->fetch()){
                                $userId = $row["userId"];
                                $username = $row["username"];
                                $hashedPassword = $row["password"];
                                $name=$row["name"];
                                $userPicture=$row["picture"];
                                if(password_verify($password, $hashedPassword)){
                                    
                                    $_SESSION["isUserLogged"] = true;
                                    $_SESSION["user"]=array(
                                        "userId"=>$userId,
                                        "username"=>$username,
                                        "name"=>$name,
                                        "picture"=>$userPicture,
                                        "role"=>$row["role"],
                                        "first_login"=>$row["first_login"],
                                    );
                                    $response=throwSuccess();
                                    $response["data"]= $_SESSION["user"];                      
                                } else{
                                    $response=throwError($loginError,-3,200);
                                }
                            }
                        } else{
                            $response=throwError($loginError,-3,200);
                        }
                    }
    
                    unset($statement);
                }
            }
            unset($pdo);
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }else{
        header("HTTP/1.0 405 Method Not Allowed");
    }
?>