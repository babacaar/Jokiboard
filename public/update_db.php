<?php
require_once "../config/controller_config_files.php";
//session_start();
$db = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
// Récupérer tous les groupes depuis la table "groups"
$query = "SELECT id, name FROM groupes";
$stmt = $db->prepare($query);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Récupérer le groupe sélectionné
  $selectedGroupId = $_POST['group_id'];

  // Mettre à jour la base de données avec le nouveau groupe sélectionné
  $query = "UPDATE pis SET group_id = :group_id";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':group_id', $selectedGroupId);
  $stmt->execute();

  // Rediriger vers la page de configuration pour éviter la soumission du formulaire à nouveau en actualisant la page
  header("Location: list.php");
  exit();
}

?>