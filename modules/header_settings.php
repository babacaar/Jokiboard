<?php require_once __DIR__ . "/../config/controller_config_files.php"; 
$siteUrl = "http://$dbhost/public";
?>
<div class="header-settings-container">
    <div id="header-settings" class="header-settings">
        <div class="dropdown-content" id="dropdown-content">
            <a tabindex="0" href='<?php echo $siteUrl . "/upload_video.php" ?>'
                title="Permet d'ajouter des vidéos">
                    <i class="fa-solid fa-video"></i> Téléverser une vidéo</a>
	    <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/agenda.php" ?>'
		title="Permet de convertir les pages de menus en image JPG">
                    <i class="fa-solid fa-calendar"></i> Agenda</a>

            <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/agendaVS.php" ?>'
                title="Permet de convertir les pages de menus en image JPG">
                    <i class="fa-solid fa-calendar"></i> Événements Vie Scolaire</a>

            <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/certificates.php" ?>'
                title="Permet de charger l'autorité de certification sur les hôtes">
                    <i class="fa-solid fa-certificate"></i> Certificats</a>


	    <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/deconnectemoi.php" ?>'
                title="Déconnexion">
                    <i class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i> Se déconnecter</a>


        </div>

        <button id="dropdown-btn" tabindex="1">
            <i id="menu-icon-bars" class="fa-solid fa-gear"></i>
            <i id="menu-icon-chevron" class="fa-solid fa-chevron-left" style="margin-left: 5px;"></i>
        </button>
    </div>
</div>

<script>
    var dropdownBtn = document.getElementById("dropdown-btn");
    var dropdownContent = document.getElementById("dropdown-content");

    var headerMenu = document.getElementById("header-settings");

    var isMenuOpen;

    // Gérer l'ouverture du menu avec le focus (touche tab)
    dropdownBtn.addEventListener("keydown", (e) => {
        if ((e.keyCode === 13 || e.keyCode === 32) && dropdownBtn === document.activeElement && !isMenuOpen) {
            openMenu(e);
        } else if ((e.keyCode === 13 || e.keyCode === 32) && dropdownBtn === document.activeElement && isMenuOpen) {
            closeMenu(e);
        }
    });

    dropdownContent.addEventListener("mouseleave", (e) => {
        if (isMenuOpen) closeMenu(e);
    });


    function openMenu(e) {
        isMenuOpen = true;

        e.preventDefault();

        setTimeout(() => {
            headerMenu.classList.add("tab-opened"); // Ajouter la classe pour indiquer l'ouverture avec Tab
            dropdownContent.querySelector("a").focus();
        }, 0);
    }


    function closeMenu(e) {
        isMenuOpen = false;

        e.preventDefault();

        setTimeout(() => {
            headerMenu.classList.remove("tab-opened"); // Supprimer la classe pour rétablir le survol
        }, 0);
    }
</script>
