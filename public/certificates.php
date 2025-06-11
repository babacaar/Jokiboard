<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Chargement autorité de certification"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="page">
        <section class="page-content">

            <form action="../controllers/controller_certificats.php" method="post" enctype="multipart/form-data">
                <h2>Téléchargement du certificat</h2>
                <hr />

                <label for="cerFile">Sélectionnez un fichier<span class="required">*</span></label><br>
                <input type="file" id="cerFile" name="cerFile" accept=".cer"><br><br>

                <label for="raspberryIP">Adresse IP du Raspberry Pi<span class="required">*</span></label><br>
                <input type="text" id="raspberryIP" name="raspberryIP"><br><br>

                <label for="username">Nom d'utilisateur<span class="required">*</span></label><br>
                <input type="text" id="username" name="username"><br><br>

                <label for="password">Mot de passe SSH<span class="required">*</span></label><br>
                <input type="password" id="password" name="password"><br><br>


                <input type="submit" name="submit" value="Télécharger">
            </form>

        </section>
    </div>
</body>


<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
