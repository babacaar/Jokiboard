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
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $name = $_POST["name"];
                    $description = $_POST["description"];

                    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

                    if (isset($_POST['add'])) {
                        $name = $_POST['name'];
                        $description = $_POST['description'];

                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $stmt = $pdo->prepare("SELECT * FROM groupes WHERE name = :name");
                        $stmt->bindParam(':name', $name);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            throw new Exception("Le nom du groupe existe déjà. Veuillez en choisir un autre.");
                        } else {
                            $insertStmt = $pdo->prepare("INSERT INTO groupes (name, description) VALUES (:name, :description)");
                            $insertStmt->bindParam(':name', $name);
                            $insertStmt->bindParam(':description', $description);

                            if ($insertStmt->execute()) {
                                $msg .= "Le groupe a bien été ajouté. <br>";
                                include "../modules/success.php";
                            } else {
                                throw new Exception("Erreur d'insertion dans la base de données.");
                            }
                        }
                    }
                }
            } catch (PDOException $e) {
                $msg .= "Erreur de connexion à la base de données : " . $e->getMessage();
                include "../modules/error.php";
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