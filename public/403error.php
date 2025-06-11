<!------------HEADER------------>
<?php
$pageTitle = "Erreur 403"; // Titre de la page
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="error page">
        <section class="page-content">
            <h1>Oups !</h1>
            <hr>
            <p>Erreur 403. Accès non autorisé.</p>

            <hr>
            <a tabindex="0" href="<?php echo isset($_SERVER['HTTPS_REFERER']) ? $_SERVER['HTTPS_REFERER'] : '#'; ?>"
                class="back-btn">
                <i class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i>Retour
            </a>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
