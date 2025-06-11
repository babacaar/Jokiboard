<?php require_once __DIR__ . "/../config/controller_config_files.php"; 
$siteUrl = "http://$dbhost/public";
?>
<section class="navigation">
    <div class="list-menu-container">

        <div class="absence-menu" id="list-menu">
             <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/modify_absence.php" ?>'
                 title="Permet de modifier ou supprimer des absences."
                 style="color: black;">Modifier ou Supprimer une absence</a>
        </div>

    </div>
</section>
