<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./css/style.css" rel="stylesheet"/>
    <?php include_once ("./utils/includes_head.html") ?>
    <title>Compte</title>
    <script src="./js/user.js"></script>
    <!-- <script src="./js/function.js"></script> -->
    
</head>
<body>
    <?php
    include_once ("./menu/navbar.html");
    if($_SESSION["user"]["first_login"]==1 && $_SERVER["REQUEST_URI"] !="/compte.html"){ ?>
    <div class="body">
    <h4>C'est votre premiere connexion, vous devez changer votre mot de passe</h4>
    
    <?php require("./password.php"); ?></div><?php
    }else{?>
        <script>
        readyProfile();
        profileEventListener();
    </script>
    
    <div class="body" id="body-profile">
        <div id="updateAccount" style="font-family: Verdana; font-size: 13px;">
            <div>
                Votre profil
            </div>
            <div style="font-family: Verdana; font-size: 13px;">
                <form name="profile-form" id="profile-form" style="overflow: hidden; margin: 10px;">
                    <input type="hidden" id="isEdit" value="" />
                    <div>
                        <div class="header-profile">Prenom</div>
                        <div class="update-profile-div">
                            <input class="edit-profile"  id="first_name" name="first_name" />
                            <label class="read-profile" id="first_name_label" name="first_name_label"></label>
                        </div>

                        <div class="header-profile">Nom de famille</div>
                        <div class="update-profile-div">
                            <input class="edit-profile"  id="last_name" name="last_name" />
                            <label class="read-profile" id="last_name_label" name="last_name_label"></label>
                        </div>

                        <div class="header-profile">Genre</div>
                        <div class="update-profile-div">
                            <div class="edit-profile"  id="gender" name="gender"></div>
                            <label class="read-profile" id="gender_label" name="gender_label"></label>
                        </div>

                        <div class="header-profile">Adresse electronique</div>
                        <div class="update-profile-div">
                            <input class="edit-profile" id="email" name="email" type="email" />
                            <label class="read-profile" id="email_label" name="email_label"></label>
                        </div>

                        <div class="header-profile">Date d'anniversaire</div>
                        <div class="update-profile-div">
                            <div class="edit-profile"  id="birthday" name="birthday"></div>
                            <label class="read-profile" id="birthday_label" name="birthday_label"></label>
                        </div>

                        <div class="header-profile">Telephone</div>
                        <div class="update-profile-div">
                            <input class="edit-profile" id="telephone" name="telephone" />
                            <label class="read-profile" id="telephone_label" name="telephone_label"></label>
                        </div>

                        <div class="header-profile">Adresse (ou vous habitez)</div>
                        <div class="update-profile-div">
                            <textarea class="edit-profile" name="home_address" id="home_address"></textarea>
                            <label class="read-profile" id="home_address_label" name="home_address_label"></label>
                        </div>
                        
                        <div class="update-profile-div">
                            <input class="edit-profile"  id="validate" type="button" value="Valider" name="validate" />
                            <input class="read-profile"  id="update" type="button" value="Modifier" name="update" />
                            <input class="edit-profile"  id="cancel" type="button" value="Annuler" name="cancel" />
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="update-pass">
        <?php include("./password.php") ?>
    </div>
    </div>

    
    <?php }?>
</body>
</html>