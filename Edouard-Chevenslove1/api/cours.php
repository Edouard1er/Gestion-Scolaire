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
            if(isset($_GET["mode"]) && $_GET["mode"]=="PROF" && $_SESSION["user"]["role"]=="PROF"){
                readCoursProf(intval($page), $pageLimit);
            }else if(isset($_GET["mode"]) && $_GET["mode"]=="SUIVI" && $_SESSION["user"]["role"]=="ELEVE"){
                readCoursSuivi($page, $pageLimit);
            }else if(isset($_GET["mode"]) && $_GET["mode"]=="ELEVE" && $_SESSION["user"]["role"]=="PROF"){
                readCoursProfEtu($page, $pageLimit);
            }
            else
                readCoursAll($page, $pageLimit);
            break;
        case 'POST':
            if(isset($_GET["type"]) && $_GET["type"]=="subscribe"){
                subscribeCours();
            }else if(isset($_GET["type"]) && $_GET["type"]=="note" && $_SESSION["user"]["role"]=="PROF"){
                giveNote();
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

    function readCoursProf($page=1, $pageLimit=10)
    {
        global $pdo, $userId;
        
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("coursId", "cours","", "statut=1 AND professeur ='".$userId."'");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            
            $sql = "SELECT c.*   
            FROM cours AS c
            WHERE c.statut=1 AND c.professeur ='".$userId."' 
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

    function readCoursProfEtu($page=1, $pageLimit=10)
    {
        global $pdo, $userId;
        
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("coursId", "cours","", "statut=1 AND professeur ='".$userId."'");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            
            $sql = "SELECT c.*, e.userId AS etudiantId, CONCAT(e.last_name,' ', e.first_name) AS etudiant_name, uc.note,uc.userCoursId, e.username as etudiant_username    
            FROM cours AS c
            INNER JOIN user_cours AS uc ON (uc.coursId=c.coursId)  
            INNER JOIN users AS e ON (uc.userId=e.userId AND e.statut=1)
            WHERE c.statut=1 AND c.professeur ='".$userId."' AND uc.statut_eleve=1 AND uc.statut_prof=1  
            ORDER BY c.coursId DESC
            LIMIT ".$offset.", ".$pageLimit;
            $cours=array();    
            if($statement = $pdo->prepare($sql)){
                $statement->bindParam(":userId", $userId, PDO::PARAM_INT);
                $statement->execute();
                if($statement->rowCount() > 0){
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        $row["description"]=addslashes($row["description"]);
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
    
    function readCoursSuivi($page=1, $pageLimit=10)
    {
        global $pdo, $userId;
        
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("coursId", "cours","INNER JOIN users ON (users.userId = cours.professeur)", "users.statut=1 AND cours.statut=1");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            
            $sql = "SELECT c.*, CONCAT(u.last_name,' ',u.first_name) as professeur_nom, uc.note, u.username as professeur_username 
            FROM cours AS c
            INNER JOIN users AS u ON (u.userId = c.professeur) 
            INNER JOIN user_cours AS uc ON (uc.coursId=c.coursId)
            WHERE c.statut=1 AND u.statut=1 AND uc.userId='".$userId."' AND uc.statut_eleve=1  AND uc.statut_prof=1
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

    function readCoursAll($page=1, $pageLimit=10){
        global $pdo, $userId;
        
        $response=array();
        $offset = ($page > 1) ? ($pageLimit * ($page - 1)) : 0;
        $totalRows=getAmountRow("coursId", "cours","INNER JOIN users ON (users.userId = cours.professeur)", "users.statut=1 AND cours.statut=1");
        	
        $pages = ($totalRows % $pageLimit == 0) ? ($totalRows / $pageLimit) : (intval($totalRows / $pageLimit) + 1);
        
        try {
            
            $sql = "SELECT c.*, CONCAT(u.last_name,' ',u.first_name) as professeur_nom, u.username as professeur_username 
            FROM cours c 
            INNER JOIN users AS u ON (c.professeur=u.userId) 
            WHERE c.coursId 
            NOT IN (SELECT uc.coursId FROM user_cours uc WHERE uc.userId = '".$userId."' AND uc.statut_eleve=1  AND uc.statut_prof=1)  
            
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
        global $pdo, $userId;
        $list_cours= json_decode(isset($_POST["list_cours"])?$_POST["list_cours"]:"");

        // Conversion du tableau en chaîne de caractères
        $list_cours_string = implode(',', array_map(function($value) use ($pdo) {
            return $pdo->quote($value);
        }, $list_cours));

        
        $sql ="
            SELECT coursId 
            FROM user_cours
            WHERE userId='".$userId."' AND coursId IN(".$list_cours_string.") AND statut_eleve='1' AND statut_prof=1";

        $alreadySubscribed=array();

        try {
            if($stmt = $pdo->prepare($sql)){
                $stmt->execute();
                if($stmt->rowCount() > 0){
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $alreadySubscribed[]=$row["coursId"];
                    }
                }
                unset($stmt);
            }
        } catch (PDOException $exception) {
            
        }
        $list_cours = array_diff($list_cours, $alreadySubscribed);
        $list_cours_final = array_map(function($value) use ($userId) {
            return [$userId, $value];
        }, $list_cours);
          
        try {
            // Préparation de la requête INSERT
            $sql = 'INSERT INTO user_cours (userId, coursId) VALUES (?, ?)';
            $stmt = $pdo->prepare($sql);
            
            // Exécution de la requête INSERT pour chaque paire de valeurs
            foreach ($list_cours_final as $value) {
                $stmt->execute($value);
            }
            $response=throwSuccess();
        } catch (PDOException $exception) {
            //throw $th;
        }
          
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function unSubscribeCours(){
        global $pdo,$_DELETE,$userId;

        $coursId= isset($_DELETE["coursId"])?intval($_DELETE["coursId"]):(isset($_GET["coursId"])?intval($_GET["coursId"]):"");
        try {
            if($coursId){
                $coursId= inputFilter($coursId);
                $sql = "UPDATE user_cours SET statut_eleve='0' WHERE coursId=? AND userId=?";
                $statement= $pdo->prepare($sql);
                
                if($statement->execute([$coursId, $userId])){
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

    function giveNote(){
        global $pdo, $userId;
        $list_cours= json_decode(isset($_POST["list_cours"])?$_POST["list_cours"]:"");
        $note=$_POST["note"];

        $list_cours_string = implode(',', array_map(function($value) use ($pdo) {
            return $pdo->quote($value);
        }, $list_cours));

        
        $sql ="
            UPDATE user_cours 
            SET note = ?
            WHERE  userCoursId IN(".$list_cours_string.") AND statut_eleve='1' AND statut_prof=1";


        try {
            if($stmt = $pdo->prepare($sql)){
                $stmt->execute([$note]);
                $response=throwSuccess();
                unset($stmt);
            }
        } catch (PDOException $exception) {
            $response=throwError();
        }
        
          
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
?>