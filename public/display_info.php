<!------------HEADER------------>
<?php
$pageTitle = "Informations"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<?php
require_once "../config/controller_config_files.php";
$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
$stmt1 = $pdo->prepare("SELECT infos FROM `Informations` ORDER BY id DESC LIMIT 1");
$stmt1->execute();
$res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
$news = $res1['infos'];
?>
</body>

<!------------BODY------------>

<body>
    <div class="alert page">
        <div id="info-banner" class="info-banner banner">
            <p id="info-message"></p>
        </div>
    </div>
</body>

<script>
    // Fonction pour afficher le bandeau d'alerte
    function showBanner(message) {
        var infoBanner = document.getElementById('info-banner');
        var infoMessage = document.getElementById('info-message');

        infoMessage.innerHTML = message;
        infoBanner.style.display = 'flex';

        // Utilisez ici une API pour la voix off, par exemple l'API Web Speech
        // Assurez-vous de vérifier la compatibilité du navigateur avec l'API Web Speech
        // if ('speechSynthesis' in window) {
        //     var msg = new SpeechSynthesisUtterance(message);
        //     window.speechSynthesis.speak(msg);
        // }
    }

    // Exemple d'utilisation : Afficher une alerte incendie
    showBanner(<?php echo json_encode($news); ?>);
</script>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
