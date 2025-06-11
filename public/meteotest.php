<!------------HEADER------------>
<?php
$pageTitle = "Météo Actuelle"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="controller page">
        <section class="page-content-tomorrow">

            <!-- <div id="weather-widget"></div> -->
            <h1>Météo Angers</h1>

            <div class="tomorrow" data-location-id="039396" data-language="FR" data-unit-system="METRIC"
                data-skin="light" data-widget-type="upcoming">
                <a href="https://www.tomorrow.io/weather-api/" rel="nofollow noopener noreferrer" target="_blank">
                    <img alt="Powered by the Tomorrow.io Weather API"
                        src="https://weather-website-client.tomorrow.io/img/powered-by.svg" />
                </a>
            </div>

            <script>
        //         const options = {
        //             method: 'GET',
        //             headers: {
        //                 'accept': 'application/json'
        //             }
        //         };

        //         fetch('https://api.tomorrow.io/v4/weather/realtime?location=angers&apikey=JzuqQZXmY1UtTRv2FZ3SGBQvsQVn9pEG', options)
        //             .then(response => response.json())
        //             .then(data => {
        //                 console.log('Full response:', data);
        //                 // Appel de la fonction pour afficher les données
        //                 displayWeather(data);
        //             })
        //             .catch(error => {
        //                 console.error(error);
        //                 // Appel de la fonction d'affichage d'erreur
        //                 displayError();
        //             });

        //         function displayWeather(data) {
        //             const widgetContainer = document.getElementById('weather-widget');

        //             // Vérifiez si les données et les propriétés attendues existent
        //             if (data.data && data.data.time && data.data.values && data.data.values.temperature && data.data.values.humidity) {
        //                 const weatherData = data.data.values;

        //                 // Affichez les données sur la page
        //                 const weatherWidget = `
        //     <p>Heure: ${data.data.time}</p>
        //     <p>Temperature: ${weatherData.temperature} °C</p>
        //     <p>Humidity: ${weatherData.humidity}%</p>
        // `;

        //                 widgetContainer.innerHTML = weatherWidget;
        //             } else {
        //                 console.error('Weather data structure is not as expected:', data);
        //                 displayError();
        //             }
        //         }


        //         function displayError() {
        //             const widgetContainer = document.getElementById('weather-widget');
        //             widgetContainer.innerHTML = '<p>Erreur lors de la récupération des données météorologiques.</p>';
        //         }

                // Widget Tomorrow Weather
                (function (d, s, id) {
                    if (d.getElementById(id)) {
                        if (window.__TOMORROW__) {
                            window.__TOMORROW__.renderWidget();
                        }
                        return;
                    }
                    const fjs = d.getElementsByTagName(s)[0];
                    const js = d.createElement(s);
                    js.id = id;
                    js.src = "https://www.tomorrow.io/v1/widget/sdk/sdk.bundle.min.js";

                    fjs.parentNode.insertBefore(js, fjs);
                })(document, 'script', 'tomorrow-sdk');
            </script>


        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
