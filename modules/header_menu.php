<div class="header-menu-container">
    <div id="header-menu" class="header-menu">
        <div class="dropdown-content" id="dropdown-content">
            <a tabindex="0" href='<?php echo $siteUrl . "/configuration.php" ?>'
                title="Permet de configurer les URL des affichages">Affichage
                Actuel</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/gestion_absence.php" ?>'
                title="Permet d'afficher les tâches qui doivent être effectuées en précisant l'ordre de priorité">Absences</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/groupe.php" ?>'
                title="Permet de créer des affichages (groupes d'hôtes)">Affichages</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/list.php" ?>'
                title="Permet de configurer les hôtes et groupes d'hôtes">Hôtes/Groupes</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/info.php" ?>'
                title="Permet d'ajouter une information ponctuelle à afficher">Informations Ponctuelles</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/vue_des_liens.php" ?>'
                title="Permet de voir l'affichage de base ">Configuration de base</a>
            <a tabindex="0" href='<?php echo $siteUrl . "/custom_style.php" ?>'
                title="Permet de modifier le style du site"><i class="fa-solid fa-paintbrush"></i> Style</a>
	    <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/configMenu.php" ?>'
		title="Permet de convertir les pages de menus en image JPG">
                    <i class="fa-solid fa-gear"></i> Configuration des Menus</a>

	    <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/deconnectemoi.php" ?>'
                title="Déconnexion">
                    <i class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i> Se déconnecter</a>
        </div>

        <button id="dropdown-btn" tabindex="1">
            <i id="menu-icon-bars" class="fa-solid fa-bars"></i>
            <i id="menu-icon-chevron" class="fa-solid fa-chevron-left"></i>
        </button>
    </div>
</div>

<script>
    var dropdownBtn = document.getElementById("dropdown-btn");
    var dropdownContent = document.getElementById("dropdown-content");

    var headerMenu = document.getElementById("header-menu");

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
