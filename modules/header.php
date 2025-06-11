<!DOCTYPE html>
<html lang="fr">

<head>
    <title>
        <?php echo $pageTitle; ?>
    </title>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css" />
    <script src="https://kit.fontawesome.com/10bb5e6754.js" crossorigin="anonymous"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>
<?php require_once __DIR__ . "/../config/controller_config_files.php"; 
$siteUrl = "http://$dbhost/public";
?>
<header>
<a  href='<?php echo $siteUrl; ?>/menu.php'>
    <img id="logo-img" class="logo-img" src="../assets/images/LOGOv1.png">
</a>

    <?php
    

    if ($dropDownMenu)
        include "header_menu.php";

    ?>
</header>

<div class="background-image"></div>

<script type="text/javascript" src="../assets/js/customStyle.js">
    // Appliquer les couleurs au chargement de la page
    document.addEventListener("DOMContentLoaded", function () {
        applyStoredStyles();
    });
</script>
