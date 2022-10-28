<?php
    session_start();
    
    if(isset($_SESSION["isUserLogged"]) && $_SESSION["isUserLogged"] === true){
        header("location: form.php");
        exit;
    }
    
    require_once ("./session/connection.php");
    require_once ("./function-php/function.php");
    
    $username = $password = $usernameError = $passwordError = $loginErrow = "";
    
    // Login - try to see if the user enters the right credentials
    if($_SERVER["REQUEST_METHOD"] == "POST"){
    
        // Test on empty entries in server side
        if(empty(inputFilter($_POST["username"]))){
            $usernameError = "Veuillez entrer un nom d'utilisateur.";
        } else{
            $username = inputFilter($_POST["username"]);
        }
        
        if(empty(inputFilter($_POST["password"]))){
            $passwordError = "Veuillez entrer un mot de passe.";
        } else{
            $password = inputFilter($_POST["password"]);
        }
        
        if(empty($usernameError) && empty($passwordError)){
            $sql = "SELECT userId, username,name,picture, password FROM users WHERE username = :username";
            
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
                                session_start();
                                
                                $_SESSION["isUserLogged"] = true;
                                $_SESSION["user"]=array(
                                    "userId"=>$userId,
                                    "username"=>$username,
                                    "name"=>$name,
                                    "picture"=>$userPicture
                                );                          
                                
                                header("location: form.html");
                            } else{
                                $loginError = "Nom d'utilisateur/mot de passe incorrect.";
                            }
                        }
                    } else{
                        $loginError = "Nom d'utilisateur/mot de passe incorrect.";
                    }
                }

                unset($statement);
            }
        }
        
        unset($pdo);
    }
?>