<section class="navigation">
    <div class="list-menu-container">
        <div class="list-menu" id="list-menu">
            <button id="list-dropdown-btn" tabindex="0">Groupes <i id="menu-icon"
                    class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-content" id="list-dropdown-content">
                <a tabindex="0" href="groupe.php">Voir les Groupes</a>
                <a tabindex="0" href="creation_groupe.php">Nouveau Groupe</a>
            </div>
        </div>

        <div class="list-menu" id="list-menu">
            <button id="list-dropdown-btn" tabindex="0">Hôtes <i id="menu-icon"
                    class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-content" id="list-dropdown-content">
                <a tabindex="0" href="list.php">Voir les Hôtes</a>
                <a tabindex="0" href="gestion_pis.php">Nouvel Hôte</a>
            </div>
        </div>
    </div>
</section>


<script>
    var listDropdownBtn = document.getElementById("list-dropdown-btn");
    var listDropdownContent = document.getElementById("list-dropdown-content");

    var listMenu = document.getElementById("list-menu");

    var isListMenuOpen;

    // Gérer l'ouverture du menu avec le focus (touche tab)
    listDropdownBtn.addEventListener("keydown", (e) => {
        if ((e.keyCode === 13 || e.keyCode === 32) && listDropdownBtn === document.activeElement && !isListMenuOpen) {
            openListMenu(e);
        } else if ((e.keyCode === 13 || e.keyCode === 32) && listDropdownBtn === document.activeElement && isListMenuOpen) {
            closeListMenu(e);
        }
    });

    listDropdownContent.addEventListener("mouseleave", (e) => {
        if (isListMenuOpen) closeListMenu(e);
    });


    function openListMenu(e) {
        isListMenuOpen = true;

        e.preventDefault();

        setTimeout(() => {
            listMenu.classList.add("tab-opened"); // Ajouter la classe pour indiquer l'ouverture avec Tab
            listDropdownContent.querySelector("a").focus();
        }, 0);
    }


    function closeListMenu(e) {
        isListMenuOpen = false;

        e.preventDefault();

        setTimeout(() => {
            listMenu.classList.remove("tab-opened"); // Supprimer la classe pour rétablir le survol
        }, 0);
    }
</script>