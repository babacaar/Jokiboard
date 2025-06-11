<!------------HEADER------------>
<?php
$pageTitle = "Informations"; // Titre de la page
$dropDownMenu = false;
include "./modules/header.php";
?>

<?php
require_once "../config/controller_config_files.php";
//$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

//$statement = $pdo->prepare("SELECT url, agendaType FROM `agendaVS` LIMIT 1");
//$statement->execute();
//$agendaFile = $statement->fetch(PDO::FETCH_ASSOC);

//$iCalFile = $agendaFile['url'];
//$iCalType = $agendaFile['agendaType'];
// Nom du fichier iCal à lire
//$iCalFile = 'google.ics';
//$iCalFile = "https://outlook.office365.com/owa/calendar/5a55001c8c8e4e3289503e89d90cd276@lpjw.fr/038ca2a4d7dd49468ce5603042a881516392841034771113203/calendar.ics";

//$iCalType = 'Google';

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

            // Extraction de la LOCATION si elle existe
            if (isset($eventData['LOCATION'])) {
                $eventData['LOCATION'] = $eventData['LOCATION'];
            } else {
                $eventData['LOCATION'] = 'Non spécifié';  // Valeur par défaut si LOCATION n'existe pas
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
                    <td>" . $event['LOCATION'] . "</td>
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

            if (preg_match('/LOCATION:(.*)\r\n/si', $eventBlock, $locRegs)) {
                // Nettoyer la chaîne et enlever les informations indésirables
                $location = str_replace("  ", " ", str_replace("\r\n", "", $locRegs[1]));

                // Supprimer les informations supplémentaires comme SEQUENCE, STATUS, etc.
                $location = preg_replace('/(SEQUENCE|STATUS|SUMMARY|TRANSP|DESCRIPTION)[^\r\n]*/i', '', $location);

                // Enlever les espaces superflus
                $location = trim($location);

                $eventData['LOCATION'] = $location;
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
                    <td>" . date('d/m/Y à H:i', $eventStartTimestamp) . "</td>
                    <td>" . $event['LOCATION'] . "</td>
                </tr>";
        }
    }
}
}

//try {
  //  $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
    //$stmt = $pdo->query("SELECT calendarTitle FROM agendaVS LIMIT 1");
    //$calendarTitle = $stmt->fetchColumn();
//} catch (PDOException $e) {
  //  echo "Erreur : " . $e->getMessage();
//}
?>
<!------------BODY------------>

<body>
    <div class="calendar page">
      <div class="calendar-container">
        <section class="page-content-calendar">
           <!-- <div class="grid-wrapper"> -->
           <!--     <div class="one"> -->
                    <h2><?php echo htmlspecialchars($calendarTitle); ?></h2>
                    <hr>
                    <table>
                        <tr>
                            <th>Événement à venir</th>
                            <th>Date</th>
                            <th>Lieu</th>
                        </tr>

                       <?php
                        // Fichier iCal à lire
                        $events = iCalDecoder($iCalFile);

                        // Vérifier si la variable $events est définie et non vide avant d'appeler displayEvents
                        if (isset($events) && !empty($events)) {
                            displayEvents($events);
                        } else {
                            // Gérer le cas où $events n'est pas défini ou est vide
                            echo "<tr><td colspan='3'>Aucun événement à afficher.</td></tr>";
                        }
                        ?>
                    </table>
           <!--     </div> -->
           <!--  </div> -->
        </section>
      </div>
    </div>
</body>

<!------------FOOTER------------>
<?php include "./modules/footer.php"; ?>

