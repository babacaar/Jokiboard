<?php
// Inclure le fichier de configuration de la base de données
require_once "../config/controller_config_files.php";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["group_id"])) {
    $group_id = $_GET["group_id"];

    try {
        // Établir la connexion à la base de données
        $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Effectuer une requête SQL pour récupérer les Raspberry Pi du groupe en fonction de l'ID du groupe
        $sql = "SELECT pis.name, pis.ip   
                FROM pis 
                INNER JOIN pis_groups ON pis.id = pis_groups.pi_id 
                WHERE pis_groups.group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->execute();

        // Récupérer les données dans un tableau associatif
        $piDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir le tableau en JSON et renvoyer la réponse
        header('Content-Type: application/json');
        echo json_encode($piDetails);
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}
?>
