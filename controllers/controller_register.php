<?php
require_once "../config/controller_config_files.php";

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère les données du formulaire
    $nom_utilisateur = htmlspecialchars($_POST['username']); // Sécurisation des données
    $email = htmlspecialchars($_POST['email']); // Sécurisation des données
    $mot_de_passe = $_POST['password'];

    // Vérifie la longueur du mot de passe
    if (strlen($mot_de_passe) < 14) {
        echo "Le mot de passe doit contenir au moins 14 caractères.";
        exit();
    }

    // Hache le mot de passe
    $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Connexion à la base de données
    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs

    try {
        // Prépare une requête pour vérifier si l'utilisateur existe déjà
        $query = "SELECT id FROM Utilisateurs WHERE nom_utilisateur = ? OR email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nom_utilisateur, $email]);
        $result = $stmt->fetch();

        // Vérifie si l'utilisateur existe déjà
        if ($result) {
            echo "Un utilisateur avec ce nom d'utilisateur ou cette adresse e-mail existe déjà.";
            exit();
        }

        // Prépare une requête pour insérer l'utilisateur dans la base de données
        $query = "INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nom_utilisateur, $email, $mot_de_passe_hache]);

        echo "Compte créé avec succès.";
    } catch (PDOException $e) {
        echo "Erreur lors de la création du compte : " . $e->getMessage();
    }

    // Ferme la connexion à la base de données
    $pdo = null;
}
?>
