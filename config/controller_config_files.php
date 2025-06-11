<?php
// Charger les variables depuis le fichier .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || trim($line) === '') continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}



$db = getenv('DB_NAME');
$dbhost = getenv('DB_HOST');
$dbuser = getenv('DB_USER');
$dbpasswd = getenv('DB_PASS');
$dbport = getenv('DB_PORT');
$Url = getenv('SITE_URL');
$names = "Myfiles";
$nom ="MyfilesInfo";

//EMPLACEMENT DU FICHIER DANS LE RASP

$dir = "$Url/scripts/Affichage_";


//CREATION DU FICHIER DANS LE RASP
$fichier1 = fopen($names, 'c+b');

?>
