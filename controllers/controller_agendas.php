<!------------HEADER------------>
<?php
$pageTitle = "Calendrier"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="controller page">
        <section class="page-content">

            <?php require_once "../config/controller_config_files.php"; ?>

            <?php
            $url = $_POST['url'];
            $agenda = $_POST['agendaType'];

            $msg = "";

            try {
                $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

                $existingAgenda = $pdo->query("SELECT * FROM agenda")->fetch(PDO::FETCH_ASSOC);


                if ($existingAgenda) {
		    $stmt = $pdo->prepare("UPDATE agenda SET url = :url, agendaType = :agendaType");
		} else {
		    $stmt = $pdo->prepare("INSERT INTO agenda (url, agendaType) VALUES (:url, :agendaType)");
                }
                    $stmt->bindParam(':url', $url);
                    $stmt->bindParam(':agendaType', $agenda);
                    $stmt->execute();

		$iCalFile = $url;
		$iCalType = $agenda;

                $msg .= " Le calendrier a été mis à jour avec succès.";
                include "../modules/success.php";

            } catch (PDOException $e) {
                $msg .= "Erreur d'insertion : " . $e->getMessage();
                include "../modules/error.php";
            } catch (Exception $e) {
                $msg .= $e->getMessage();
                include "../modules/error.php";
            }
            $pdo = null;
            ?>

        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
