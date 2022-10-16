<?php session_start();?>
<div id="navbar">
    <script type="text/javascript">
        $(document).ready(function () {
            // Create a jqxMenu
            $("#jqxMenu").jqxMenu({ width: '100%', height: '20px'});
            var centerItems = function () {
                var firstItem = $($("#jqxMenu ul:first").children()[0]);
                firstItem.css('margin-left', 0);
                var width = 0;
                var borderOffset = 2;
                $.each($("#jqxMenu ul:first").children(), function () {
                    width += $(this).outerWidth(true) + borderOffset;
                });
                var menuWidth = $("#jqxMenu").outerWidth();
                firstItem.css('margin-left', (menuWidth / 2 ) - (width / 2));
            }
            centerItems();
                $(window).resize(function () {
                    centerItems();
            });
            $("#jqxMenu").css('visibility', 'visible');
        });
    </script>
    <div id='jqxWidget' style='height: 200px;'>
        <div id='jqxMenu' style='visibility: hidden; margin-left: 5px;'>
            <ul>
                <li><a href="./form.php">Accueil</a></li>
                <li type='separator'></li>
                <li><img src="/user-img/<?= $_SESSION["user"]["picture"] ?>" class="img-login" alt="<?= $_SESSION["user"]["name"] ?>" />
                    <ul style='width: 220px;'>
                        <li><a href="./compte.php">Voir le profil</a></li>
                        <li><a href="./logout.php">Deconnecter</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>