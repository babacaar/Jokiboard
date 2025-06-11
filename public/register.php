<?php
$pageTitle = "Envoi"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<body>
    <div class="page">
        <section class="page-content">
          <div class="container">
            <form action="../controllers/controller_register.php" method="post">
              <h1>Création de compte</h1>
              <hr>

              <label for="username">Nom d'utilisateur:</label><br>
              <input type="text" id="username" name="username" required><br>

              <label for="email">Adresse e-mail:</label><br>
              <input type="email" id="email" name="email" required><br>

              <label for="password">Mot de passe (au moins 14 caractères):</label><br>
              <input type="password" id="password" name="password" minlength="14" required><br>

             <button class="submit-btn" type="submit" class="btn btn-primary">Créer un compte</button>

            </form>
          </div>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
