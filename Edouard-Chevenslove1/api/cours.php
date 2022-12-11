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
            if(isset($_GET["mode"]) && $_GET["mode"]=="PROF" && $_SESSION["user"]["role"]=="PROF")
                readCours(intval($page), $pageLimit,true);
            else
                readCours($page, $pageLimit);
            break;
        case 'POST':
            if(isset($_GET["type"]) && $_GET["type"]=="subscribe"){
                subscribeCours();
            }else{
                if(isset($_POST["nom"])){
                    $nom=htmlentities($_POST["nom"]);
                    $description = isset($_POST["description"])?$_POST["description"]:"";
                    createCours($nom,$description);
                }else{
                    header('Content-Type: application/json');
                    echo json_encode(throwError("no cours"), JSON_PRETTY_PRINT);
                }
            }
            break;
        case 'PUT':
            updateCours();
            break; 
        case 'DELETE':
            if(isset($_GET["type"]) && $_GET["type"]=="subscribe"){
                unSubscribeCours();
            }else{
                deleteCours();
            }
            break;
        default:
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }

    function createCours($nom,$description){
        global $pdo, $userId;
        
        $response=array();
        if($_SESSION["user"]["role"]=="ADMIN" || $_SESSION["user"]["role"]=="PROF"){
            try {
                $sql = "INSERT INTO cours (professeur, nom,description) VALUES (:professeur, :nom, :description)";
    
                if($statement = $pdo->prepare($sql)){
                    $statement->bindParam(":professeur", $userId, PDO::PARAM_INT);
                    $statement->bindParam(":nom", $nom, PDO::PARAM_STR);
                    $statement->bindParam(":description", $description, PDO::PARAM_STR);
                    if($statement->execute()){
                        $response=array(
                            "message"=>"Course publiee avec succes !",
                            "code"=>1
                        );
                        $response["data"]["id"]=$pdo->lastInsertId();
                    }
                    unset($statement);
                }
            } catch(PDOException $exception){ 
                $response=throwError();   
            }
        }else{
            $response=throwError("Vous n'avez pas droit a cette fonctionnalite, veuillez contacter votre administrateur", 0, 403);
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    function readCours($page=1, $pageLimit=10,$isProf=false)
    {
        global $pdo, $userId;
        echo $isProf;
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("coursId", "cours","INNER JOIN users ON (users.userId = cours.professeur)", "users.statut=1 AND cours.statut=1");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            $sql = "SELECT c.*, u.username, u.last_name FROM cours AS c
            INNER JOIN users AS u ON (u.userId = c.professeur) 
            WHERE c.statut=1 AND u.statut=1 ".($isProf?("AND c.professeur ='".$userId."'"):"")." 
            ORDER BY c.coursId DESC
            LIMIT ".$offset.", ".$pageLimit;
            $cours=array();    
            if($statement = $pdo->prepare($sql)){
                $statement->bindParam(":userId", $userId, PDO::PARAM_INT);
                $statement->execute();
                if($statement->rowCount() > 0){
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        if($row["professeur"]==$_SESSION["user"]["userId"]){
                            $row["editable"]=1;
                        }else{
                            $row["editable"]=0; 
                        }
                        $cours[]=$row;
                    }
                }
            }
            $response = [
                'total' => $totalRows,
                'current' => $page,
                'from' => ($offset + 1),
                'to' => ($offset + $pageLimit),
                'pages' => $pages,
                'cours'=>$cours
            ];
        } catch(PDOException $exception){ 
            echo $exception;
            $response=throwError();     
        } 
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    function updateCours()
    {
        global $_PUT, $userId;
        $coursId= isset($_PUT["coursId"])?intval($_PUT["coursId"]):(isset($_GET["coursId"])?intval($_GET["coursId"]):"");
        $where="professeur='".$userId."' AND coursId='".$coursId."'";
        $exceptionField=array("statut");
        $response=updateTable("cours",$coursId, $_PUT,"coursId",$where,0,$exceptionField);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function deleteCours(){
        global $pdo,$_DELETE,$userId;
        try {
            if(isset($_DELETE["coursId"])){
                $coursId= inputFilter($_DELETE["coursId"]);
                $sql = "UPDATE cours SET statut='0' WHERE coursId=? AND professeur=?";
                $statement= $pdo->prepare($sql);
                
                if($statement->execute([$coursId, $userId])){
                    $response=array(
                        'status' => 1,
                        'status_message' =>'Cours supprime avec succes.'
                    );
                }
            }else{
                $response=throwError(); 
            }
            
        } catch (PDOException $exception) {
            $exception;
            $response=throwError(); 
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function subscribeCours(){
        global $userId;
        $coursId= isset($_POST["coursId"])?intval($_POST["coursId"]):(isset($_GET["coursId"])?intval($_GET["coursId"]):"");
        $where=" userId='".$userId."' AND coursId='".$coursId."'AND statut_eleve='1'";
        $count=getAmountRow("userCoursId", "user_cours", "", $where);
        if($count==0){
            $_POST["userId"]=$userId;
            $response=insertRow("user_cours",$_POST);
        }else{
            $response=throwError("Cours inscrit deja");
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function unSubscribeCours(){
        global $pdo,$_DELETE,$userId;

        $userCoursId= isset($_DELETE["userCoursId"])?intval($_DELETE["userCoursId"]):(isset($_GET["userCoursId"])?intval($_GET["userCoursId"]):"");
        try {
            if($userCoursId){
                $userCoursId= inputFilter($userCoursId);
                $sql = "UPDATE user_cours SET statut_eleve='0' WHERE userCoursId=? AND userId=?";
                $statement= $pdo->prepare($sql);
                
                if($statement->execute([$userCoursId, $userId])){
                    $response=array(
                        'status' => 1,
                        'status_message' =>'Cours unsubscribed avec succes.'
                    );
                }
            }else{
                $response=throwError(); 
            }
            
        } catch (PDOException $exception) {
            $exception;
            $response=throwError(); 
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
?>