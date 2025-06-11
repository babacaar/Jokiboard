<!------------HEADER------------>
<?php
$pageTitle = "Alerte Incendie / Alerte PPMS"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>

  <div class="alert page">
    <div id="alert-banner" class="banner blink" >
      <p id="alert-message">
        Alerte Incendie en cours. <br>
        Veuillez évacuer le bâtiment.
      </p>
    </div>
  </div>

  <script>
    // Fonction pour afficher le bandeau d'alerte
    function showBanner(message) {
      var alertBanner = document.getElementById("alert-banner");
      var alertMessage = document.getElementById("alert-message");

      alertMessage.innerHTML = message;
      alertBanner.style.display = "flex";

      // Utilisez ici une API pour la voix off, par exemple l'API Web Speech
      // Assurez-vous de vérifier la compatibilité du navigateur avec l'API Web Speech
      // if ("speechSynthesis" in window) {
      //   var msg = new SpeechSynthesisUtterance(message);
      //   window.speechSynthesis.speak(msg);
      // }
    }

    // Exemple d'utilisation : Afficher une alerte incendie
    showBanner("Alerte Incendie en cours. <br> Veuillez évacuer le bâtiment.");
  </script>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
