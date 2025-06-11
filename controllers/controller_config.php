<!------------HEADER------------>
<?php
$pageTitle = "Configuration"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>

	<div class="controller page">
		<section class="page-content">

			<?php
			require_once "../config/controller_config_files.php";
			$msg = "";

			try {
				// Vérifie qu'il provient d'un formulaire
				if ($_SERVER["REQUEST_METHOD"] == "POST") {

					$Conf_sites = $_POST["Conf_sites0"];
					date_default_timezone_set('Europe/Paris');
					$Conf_date = date('y-m-d à H:i:s');
					$msg .= $Conf_date;
					//$Conf_date  = $_POST["Conf_date0"];
					if (empty($Conf_sites)) {
						throw new Exception("Merci de remplir le champ");
					}

					// Ouvrir une nouvelle connexion au serveur MySQL
					$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

					if (!$pdo) {
						throw new Exception("Erreur de connexion à la base de données");
					}

					// Préparer la requête d'insertion SQL
					$statement = $pdo->prepare("INSERT INTO configuration (Conf_sites, Conf_date) VALUES(:Conf_sites, NOW())");

					// Associer les valeurs et exécuter la requête d'insertion
					$statement->bindParam(':Conf_sites', $Conf_sites, PDO::PARAM_STR);

					if (!$statement->execute()) {
						throw new Exception("Erreur MySQL : " . $statement->errorInfo()[2]);
					}

					$msg .= "Le lien suivant : " . $Conf_sites . " a bien été ajouté.";
					include "../modules/success.php";
				}
			} catch (Exception $e) {
				$msg .= $e->getMessage();
				include "../modules/error.php";
			}
			?>

		</section>
	</div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
