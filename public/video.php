<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Afficher les informations sur les fichiers téléchargés
var_dump($_FILES);

// Vérifier si le formulaire a été soumis et si un fichier a été téléchargé
if(isset($_POST["submit"]) && isset($_FILES["video"])) {
    $targetDir = "$Url/Videos/";
    $targetFile = $targetDir . basename($_FILES["video"]["name"]);
    
    // Afficher le type de fichier
    $videoFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    echo "Type de fichier : " . $videoFileType;

    // Vérifier si le fichier vidéo est une vidéo réelle ou une fausse vidéo
    $check = getimagesize($_FILES["video"]["tmp_name"]);
    if($check !== false) {
        echo "Le fichier est une vidéo.";
    } else {
        echo "Le fichier n'est pas une vidéo.";
    }

    // Afficher les éventuelles erreurs lors du téléchargement
    if ($_FILES["video"]["error"] > 0) {
        echo "Erreur de téléchargement : " . $_FILES["video"]["error"];
    } else {
        // Si tout est OK, essayez de télécharger le fichier
        if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
            echo "Le fichier ". htmlspecialchars(basename($_FILES["video"]["name"])). " a été téléchargé.";
        } else {
            echo "Une erreur est survenue lors du téléchargement de votre fichier.";
        }
    }
}
?>
