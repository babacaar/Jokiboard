<?php
// Démarre la session
session_start();

// Détruit toutes les données de la session
session_destroy();

// Redirige l'utilisateur vers la page de connexion
header("Location: connexion.php");
exit;
?>
