<?php
$pageTitle = "Connexion"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<body>
    <div class="page">
        <section class="page-content">
	  <div class="container">
	    <form action="../controllers/controller_connexion.php" method="post">
              <h1>Connexion</h1>
              <hr>

              <label for="username">Nom d'utilisateur</label><br>
              <input type="text" id="username" name="username" required><br>

              <label for="password">Mot de passe</label><br>
              <input type="password" id="password" name="password" required><br>

             <button class="submit-btn" type="submit" class="btn btn-primary">Se connecter</button>
            </form>
	    <br><br><br><br>
	  </div>
          <a href="register.php">Créer un compte</a>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
