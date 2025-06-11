<?php
// Démarrage de la session
session_start();

require_once "../config/controller_config_files.php";
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit();
}
// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location:/public/connexion.php");
    exit(); // Arrêt de l'exécution du script pour éviter tout accès non autorisé
}

?>
