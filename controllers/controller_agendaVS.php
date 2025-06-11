<!------------HEADER------------>
<?php
$pageTitle = "Calendrier Vie Scolaire";
$dropDownMenu = true;
include "../modules/header.php";
?>

<body>
    <div class="controller page">
        <section class="page-content-calendrier">
<?php
require_once "../config/controller_config_files.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;

    try {
        $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

        if ($action === 'add') {
            // Récupérer les données du formulaire
            $url = $_POST['url'];
            $agendaType = $_POST['agendaType'];
            $calendarTitle = $_POST['calendarTitle'];

            // Insérer dans la base de données
            $stmt = $pdo->prepare("INSERT INTO agendaVS (url, agendaType, calendarTitle) VALUES (:url, :agendaType, :calendarTitle)");
            $stmt->bindParam(':url', $url);
            $stmt->bindParam(':agendaType', $agendaType);
            $stmt->bindParam(':calendarTitle', $calendarTitle);
            $stmt->execute();

            // Générer le nom de fichier pour la nouvelle page
            $fileName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $calendarTitle) . '.php'; // Remplace les caractères spéciaux par "_"
            $filePath = __DIR__ . '/../' . $fileName;

            // Contenu de la nouvelle page
            $newPageContent = <<<PHP
<?php
\$iCalFile = "{$url}";
\$iCalType = "{$agendaType}";
\$calendarTitle = "{$calendarTitle}";

require "display_eventVSCO.php"; // Réutilise un fichier template commun
PHP;

            // Écrire le contenu dans le nouveau fichier
            if (file_put_contents($filePath, $newPageContent) === false) {
                throw new Exception("Erreur lors de la création de la page {$fileName}");
            }

            echo "Calendrier ajouté avec succès. La page a été créée : <a href='{$fileName}'>{$fileName}</a>";
        } elseif ($action === 'edit' && $id) {
            // Récupérer les données pour modification
            $stmt = $pdo->prepare("SELECT * FROM agendaVS WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $agendaData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($agendaData) {
                echo "<form action='controller_agendaVS.php' method='post'>
                        <input type='hidden' name='action' value='update'>
                        <input type='hidden' name='id' value='" . htmlspecialchars($agendaData['id']) . "'>
                        <label>Lien de l'agenda :</label>
                        <input type='text' name='url' value='" . htmlspecialchars($agendaData['url']) . "' required>
                        <label>Titre :</label>
                        <input type='text' name='calendarTitle' value='" . htmlspecialchars($agendaData['calendarTitle']) . "' required>
                        <label>Type :</label>
                        <select name='agendaType' required>
                            <option value='Google'" . ($agendaData['agendaType'] === 'Google' ? ' selected' : '') . ">Google</option>
                            <option value='Outlook'" . ($agendaData['agendaType'] === 'Outlook' ? ' selected' : '') . ">Outlook</option>
                        </select>
                        <button type='submit'>Mettre à jour</button>
                      </form>";
            } else {
                echo "Calendrier introuvable.";
            }

        } elseif ($action === 'update' && $id) {
            // Mettre à jour un calendrier existant
            $stmt = $pdo->prepare("UPDATE agendaVS SET url = :url, agendaType = :agendaType, calendarTitle = :calendarTitle WHERE id = :id");
            $stmt->bindParam(':url', $_POST['url']);
            $stmt->bindParam(':agendaType', $_POST['agendaType']);
            $stmt->bindParam(':calendarTitle', $_POST['calendarTitle']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo "Calendrier mis à jour avec succès.";

        } elseif ($action === 'delete' && $id) {
            // Supprimer un calendrier
            $stmt = $pdo->prepare("DELETE FROM agendaVS WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            echo "Calendrier supprimé avec succès.";
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
        </section>
    </div>
</body>
