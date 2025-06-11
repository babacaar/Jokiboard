<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Configuration de Base"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="page">
        <section class="page-content">
            <h1>Liens de Base</h2>
            <hr>

            <div class="links">
                <?php
                require_once "../config/controller_config_files.php";
                $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
                $stmt0 = $pdo->prepare("SELECT * FROM `configuration` WHERE  Conf_id= '1'");
                $stmt0->execute();
                $res0 = $stmt0->fetchall();
                foreach ($res0 as $row0) {
                    $links = explode(" ", $row0['Conf_sites']);
                    $id = $row0['Conf_id'];
                    $dateLastModif = $row0['Conf_date'];
                    date_default_timezone_set('Europe/Paris');
                    $Conf_date = date('j-M-Y à H:i:s');
		    echo "<div>"; // Commence une nouvelle ligne
                    foreach ($links as $link) {
                        echo "<a style='color: black;' href='" . $link . "' target='_blank' class='link'>" . $link . "</a><br>"; // Affiche chaque lien cliquable dans un nouvel onglet
                    }
   		    echo "</div>";
    		    echo "<br>";
            	}

                ?>
            </div>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
