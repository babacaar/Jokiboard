<!------------HEADER------------>
<?php
$pageTitle = "Informations"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<?php
require_once "../config/controller_config_files.php";
$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
$stmt1 = $pdo->prepare("SELECT * FROM `absence` ORDER BY fin_absence DESC, nom ASC LIMIT 8");
//$stmt1->bindValue(':id', $id);
//$stmt1->bindParam(1, $id);
$stmt1->execute();
$res1 = $stmt1->fetchall();


$statement = $pdo->prepare("SELECT url, agendaType FROM `agenda`");
$statement->execute();
$agendaFile = $statement->fetch(PDO::FETCH_ASSOC);

$iCalFile = $agendaFile['url'];
$iCalType = $agendaFile['agendaType'];


// Fonction iCalDecoder
if ($iCalType === 'Outlook'){
function iCalDecoder($file)
{
    $ical = file_get_contents($file);
    // preg_match_all analyse $ical pour trouver l'expression qui correspond au pattern '/(BEGIN:VEVENT.*?END:VEVENT)/si'
    // on extrait ainsi les blocs d'événements entre BEGIN:VEVENT et END:VEVENT (balises de fichier iCal)
    preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);

    $icalArray = array();

    foreach ($result[0] as $eventBlock) {

        // on divise les lignes du bloc d'événement en utilisant le retour chariot comme délimiteur
        $eventLines = explode("\r\n", $eventBlock);

        $eventData = array();

        foreach ($eventLines as $item) {

            // on divise chaque ligne en deux parties (clé et valeur) en utilisant le caractère ":" comme délimiteur
            $lineParts = explode(":", $item);

            if (count($lineParts) > 1) {
                $eventData[$lineParts[0]] = $lineParts[1];
            }
        }

        // Vérifier si la clé "DTSTART" existe avant de l'ajouter au tableau des données d'événement
        if (isset($eventData['DTSTART;TZID=Romance Standard Time'])) {
            if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $eventBlock, $regs)) {
                $eventData['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
            }

            // condition pour ne pas afficher les événements passés
            $eventStartDate = strtotime($eventData['DTSTART;TZID=Romance Standard Time']);
            $now = time();
            if ($eventStartDate > $now) {
                $icalArray[] = $eventData;
            }
        }
    }

    // Trier les événements par date et heure de début
     usort($icalArray, function ($a, $b) {
        return strtotime($a['DTSTART;TZID=Romance Standard Time']) - strtotime($b['DTSTART;TZID=Romance Standard Time']);
    });

    // Limiter le nombre d'événements à 7
    $icalArray = array_slice($icalArray, 0, 7);

    return $icalArray;
}

// Fonction d'affichage des événements
function displayEvents($events)
{
    $now = time(); // Timestamp actuel

    // Tri des événements par date de début
   usort($events, function ($a, $b) {
       return strtotime($a['DTSTART;TZID=Romance Standard Time']) - strtotime($b['DTSTART;TZID=Romance Standard Time']);
    });

    foreach ($events as $event) {
        $eventStartTimestamp = strtotime($event['DTSTART;TZID=Romance Standard Time']);
        // Vérifier si l'événement est à venir
        if ($eventStartTimestamp > $now) {
            echo "
                <tr>
                    <td>" . $event['SUMMARY'] . "</td>
                    <td >" . date('d/m/Y à H:i', $eventStartTimestamp) . "</td>
                </tr>";
        }
    }
}
}
else if ($iCalType === 'Google' ){
// Fonction iCalDecoder
function iCalDecoder($file)
{
    $ical = file_get_contents($file);
    // preg_match_all analyse $ical pour trouver l'expression qui correspond au pattern '/(BEGIN:VEVENT.*?END:VEVENT)/si'
    // on extrait ainsi les blocs d'événements entre BEGIN:VEVENT et END:VEVENT (balises de fichier iCal)
    preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);

    $icalArray = array();

    foreach ($result[0] as $eventBlock) {

        // on divise les lignes du bloc d'événement en utilisant le retour chariot comme délimiteur
        $eventLines = explode("\r\n", $eventBlock);

        $eventData = array();

        foreach ($eventLines as $item) {
            // on divise chaque ligne en deux parties (clé et valeur) en utilisant le caractère ":" comme délimiteur
            $lineParts = explode(":", $item);

            if (count($lineParts) > 1) {
                $eventData[$lineParts[0]] = $lineParts[1];
            }
        }

        // Vérifier si la clé "DTSTART" existe avant de l'ajouter au tableau des données d'événement
        if (isset($eventData['DTSTART'])) {
            if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $eventBlock, $regs)) {
                $eventData['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
            }

            // condition pour ne pas afficher les événements passés
            $eventStartDate = strtotime($eventData['DTSTART']);
            $now = time();
            if ($eventStartDate > $now) {
                $icalArray[] = $eventData;
            }
        }
    }

    // Trier les événements par date et heure de début
    usort($icalArray, function ($a, $b) {
      return strtotime($a['DTSTART']) - strtotime($b['DTSTART']);
   });

    // Limiter le nombre d'événements à 3
    $icalArray = array_slice($icalArray, 0, 3);

   return $icalArray;
}

// Fonction d'affichage des événements
function displayEvents($events)
{
    $now = time(); // Timestamp actuel

    // Tri des événements par date de début
   usort($events, function ($a, $b) {
     return strtotime($a['DTSTART']) - strtotime($b['DTSTART']);
   });

    foreach ($events as $event) {
        $eventStartTimestamp = strtotime($event['DTSTART']);
        // Vérifier si l'événement est à venir
        if ($eventStartTimestamp > $now) {
            echo "
                <tr>
                    <td>" . $event['SUMMARY'] . "</td>
                    <td >" . date('d/m/Y à H:i', $eventStartTimestamp) . "</td>
                </tr>";
        }
    }
}
}
?>
<!------------BODY------------>

<body>
    <div class="absences page">
        <section class="page-content-tomorrow">
            <div class="grid-wrapper">
                <div class="one">
                    <h2>Absences du Personnel</h2>
                    <hr>
                    <table>
                        <tr>
                            <th>NOM Prénom</th>
                            <!-- <th>Motif</th> -->
                            <th>Date de Début</th>
                            <th>Date de Fin</th>
                        </tr>

                        <?php
                        date_default_timezone_set('Europe/Paris');

                        foreach ($res1 as $row1) {
                            $name = $row1['nom'];
                            $fname = $row1['prenom'];
                            //$motif = $row1['motif'];
                            $dateDebut = date('d/m/Y', strtotime($row1['debut_absence']));
                            $dateFin = date('d/m/Y', strtotime($row1['fin_absence']));
                            // Conversion des dates de début et de fin en timestamp
                            $dateFinTimestamp = strtotime($row1['fin_absence']);
                            $dateActuelle = strtotime(date('Y-m-d')); // Date actuelle

                            // Vérifie si la date de fin est postérieure à la date actuelle
                            if ($dateFinTimestamp >= $dateActuelle) {
                                echo "<tr><td>" . $name . " " . $fname . "<td>" . $dateDebut . "<td>" . $dateFin . "</tr>";
                            }
                        }
                        ?>
                    </table>

                    <h2>Calendrier</h2>
                    <hr>
                    <table>
                        <tr>
                            <th>Événement à venir</th>
                            <th>Date</th>
                        </tr>

                       <?php
                        // Fichier iCal à lire
                        $events = iCalDecoder($iCalFile);

                        // Vérifier si la variable $events est définie et non vide avant d'appeler displayEvents
                        if (isset($events) && !empty($events)) {
                            displayEvents($events);
                        } else {
                            // Gérer le cas où $events n'est pas défini ou est vide
                            echo "<tr><td colspan='2'>Aucun événement à afficher.</td></tr>";
                        }
                        ?>
                    </table>
                </div>

               <div class="two">
                    <h2>Météo de la Semaine</h2>
                    <hr>

                    <div id="ww_3c6e5842e3df1" v='1.3' loc='id' a='{"t":"responsive","lang":"fr","sl_lpl":1,"ids":["wl7516"],"font":"Arial","sl_ics":"one_a","sl_sot":"celsius","cl_bkg":"#FFFFFF","cl_font":"#000000","cl_cloud":"#d4d4d4","cl_persp":"#2196F3","cl_sun":"#FFC107","cl_moon":"#FFC107","cl_thund":"#FF5722","cl_odd":"#0000000a"}'>
                        Plus de prévisions: <a href="https://oneweather.org/fr/paris/20_jours/" id="ww_3c6e5842e3df1_u" target="_blank">Météo 20 jours</a>
                    </div>
                    <script async src="https://app2.weatherwidget.org/js/?id=ww_3c6e5842e3df1"></script>
                </div>
            </div>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php";

