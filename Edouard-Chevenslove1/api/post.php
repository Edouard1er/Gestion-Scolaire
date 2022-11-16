<?php
    // session_start();
    require "../session/connection.php";
    require "../function-php/function.php";
    require "../utils/put_method_delete.php";
    require "../utils/start.php";
    $request_method = $_SERVER['REQUEST_METHOD'];
    $userId=$_SESSION["user"]["userId"];
    switch($request_method)
    {
        case 'GET':
            $page = (isset($_GET['page']) && $_GET['page'] > 0) ? intval($_GET['page']) : 1;
            $pageLimit=10;
            if(!empty($_GET["id"]))
                readPost(intval($page), $pageLimit,$_GET["id"]);
            else
                readPost($page, $pageLimit);
            break;
        case 'POST':
            if(verifyPostValue("post-content")){
                $post=htmlentities($_POST["post-content"]);
                createPost($userId,$post);
            }else{
                header('Content-Type: application/json');
                echo json_encode(throwError("no post"), JSON_PRETTY_PRINT);
            }
            break;
        case 'PUT':
            updatePost($userId);
            break; 
        case 'DELETE':
            deletePost($userId);
            break;
        default:
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }

    function createPost($userId,$post){
        global $pdo;
        
        $response=array();

        try {
            $sql = "INSERT INTO posts (userId, contenu,created_at) VALUES (:userId, :contenu, :created_at)";

            $contenu = $post;
            $created_at= date("Y-m-d H:i");
            if($statement = $pdo->prepare($sql)){
                $statement->bindParam(":userId", $userId, PDO::PARAM_INT);
                $statement->bindParam(":contenu", $contenu, PDO::PARAM_STR);
                $statement->bindParam(":created_at", $created_at, PDO::PARAM_STR);
                if($statement->execute()){
                    $response=array(
                        "message"=>"Poste publiee avec succes !",
                        "code"=>1
                    );
                }
                unset($statement);
            }
        } catch(PDOException $exception){ 
            $response=throwError();   
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    function readPost($page=1, $pageLimit=10,$userId="")
    {
        global $pdo;
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("postId", "posts","INNER JOIN users ON (users.userId = posts.userId)", "users.statut=1 AND posts.statut=1");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            $sql = "SELECT p.*, u.username, u.name FROM posts AS p
            INNER JOIN users AS u ON (u.userId = p.userId) 
            WHERE p.statut=1 AND u.statut=1 ".($userId?("AND u.userId ='".$userId."'"):"")." 
            ORDER BY p.postId DESC
            LIMIT ".$offset.", ".$pageLimit;
            $posts=array();    
            if($statement = $pdo->prepare($sql)){
                $statement->bindParam(":userId", $userId, PDO::PARAM_INT);
                $statement->execute();
                if($statement->rowCount() > 0){
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        if($row["userId"]==$_SESSION["user"]["userId"]){
                            $row["editable"]=1;
                        }else{
                            $row["editable"]=0; 
                        }
                        $posts[]=$row;
                    }
                }
            }
            $response = [
                'total' => $totalRows,
                'current' => $page,
                'from' => ($offset + 1),
                'to' => ($offset + $pageLimit),
                'pages' => $pages,
                'posts'=>$posts
            ];
        } catch(PDOException $exception){ 
            echo $exception;
            $response=throwError();     
        } 
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    function updatePost($userId)
    {
        global $pdo,$_PUT;
        try {
            if(isset($_PUT["postId"]) && isset($_PUT["post"])){
                $postId= inputFilter($_PUT["postId"]);
                $contenu=htmlentities($_PUT["post"]);
                $sql = "UPDATE posts SET contenu=? WHERE postId=? AND userId=?";
                $statement= $pdo->prepare($sql);
                
                if($statement->execute([$contenu,$postId, $userId])){
                    $response=array(
                        'status' => 1,
                        'status_message' =>'Poste modifie avec succes.'
                    );
                }
            }else{
                $response=throwError("No post"); 
            }
            
        } catch (PDOException $exception) {
            $response=throwError(); 
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function deletePost($userId){
        global $pdo,$_DELETE;
        try {
            if(isset($_DELETE["postId"])){
                $postId= inputFilter($_DELETE["postId"]);
                $sql = "UPDATE posts SET statut='0' WHERE postId=? AND userId=?";
                $statement= $pdo->prepare($sql);
                
                if($statement->execute([$postId, $userId])){
                    $response=array(
                        'status' => 1,
                        'status_message' =>'Poste supprime avec succes.'
                    );
                }
            }else{
                $response=throwError("No post"); 
            }
            
        } catch (PDOException $exception) {
            $exception;
            $response=throwError(); 
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
?>