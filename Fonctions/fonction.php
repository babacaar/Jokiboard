<?php
// Démarrage de la session
//session_start();

// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . "/../config/controller_config_files.php";

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit();
}



// Fonction pour récupérer l'ID du rôle de l'utilisateur
function getUserRoleId($user_id, $pdo) {
    $query = "SELECT id_role FROM Utilisateurs_Roles WHERE id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam('id_utilisateur', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['id_role'];
}

// Fonction pour vérifier les permissions d'accès à une page
function checkPermission($role_id, $page, $pdo) {
    // Requête SQL pour vérifier si le rôle a la permission pour la page donnée
    $query = "SELECT COUNT(*) AS count FROM Roles_Permissions rp
              INNER JOIN Permissions p ON rp.id_permission = p.id
              WHERE rp.id_role = :role_id AND p.page = :page";
    // Préparation de la requête
    $stmt = $pdo->prepare($query);
    // Liaison des paramètres
    $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
    $stmt->bindParam(':page', $page, PDO::PARAM_STR);
    // Exécution de la requête
    $stmt->execute();
    // Récupération du résultat
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Retourne vrai si le rôle a la permission pour la page, sinon faux
    return $row['count'] > 0;
}

// Vérification des permissions avant d'accéder à une page restreinte
if (isset($_SESSION['id_utilisateur'])) { // Vérification si l'utilisateur est connecté
    $user_id = $_SESSION['id_utilisateur']; // Récupération de l'ID de l'utilisateur connecté
    $role_id = getUserRoleId($user_id, $pdo); // Récupération de l'ID du rôle de l'utilisateur connecté depuis la session
    $page = "list.php"; // Récupération du nom de la page actuelle
    if (!checkPermission($role_id, $page, $pdo)) {
        // Redirection vers une page d'erreur d'autorisation
        header("Location: 403error.php");
        exit(); // Arrêt de l'exécution du script pour éviter tout accès non autorisé
    }
} else {
    // Redirection vers une page de connexion si l'utilisateur n'est pas connecté
    header("Location: connnexion.php");
    exit(); // Arrêt de l'exécution du script pour éviter tout accès non autorisé
}
?>
