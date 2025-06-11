<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Style Personnalisé"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="custom-style page">

        <section class="page-content">
            <form method="post" action="" id="action-form">
                <h1>Style Personnalisé</h1>

                <fieldset>
                    <legend>
                        <h2>Couleurs</h2>
                    </legend>
                    <div>
                        <input type="color" id="primary" name="primary" value="#ff721f"
                             />
                        <label for="primary">Couleur Principale</label>
                    </div>

                    <div>
                        <input type="color" id="secondary" name="secondary" value="#0099dc"
                             />
                        <label for="secondary">Couleur Secondaire</label>
                    </div>

                    <div>
                        <input type="color" id="background-color" name="background-color" value="#ff721f"
                             />
                        <label for="background-color">Couleur d'Arrière Plan</label>
                    </div>

                </fieldset>

                <fieldset>
                    <legend>
                        <h2>Images</h2>
                    </legend>

                    <div>
                        <label for="background-image-url">Image d'Arrière Plan</label>
                        <input type="text" id="background-image-url" 
                            value='https://osticket.lpjw.net/osticket/scp/logo.php?backdrop'
                            placeholder="Entrez l'url de l'arrière plan" />
                    </div>
                    <span><i class="fa-solid fa-circle-info colored-icon"></i> Laissez le champ vide pour enlever
                        l'image
                        d'Arrière Plan</span>

                    <div>
                        <label for="logo-url">Logo <span class="required">*</span></label>
                        <input type="text" id="logo-url"  value='./images/logo_transparent.png'
                            placeholder="Entrez l'url du logo" required />
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <h2>Bandeau d'alerte</h2>
                    </legend>
                    <div>
                        <input type="color" id="alert-color" name="alert-color" value="#d61757"
                             />
                        <label for="alert-color">Couleur du Bandeau d'Alerte</label>
                    </div>
                    <div>
                        <input type="color" id="alert-text" name="alert-text" value="#ffffff"
                             />
                        <label for="alert-text">Couleur du Texte</label>
                    </div>

                    <div class="checkbox">
                        <input type="checkbox" name="alert-blink-checkbox" id="alert-blink-checkbox"
                            >
                        <label for="alert-blink-checkbox">Ajouter Clignotement</label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <h2>Bandeau d'informations</h2>
                    </legend>
                    <div>
                        <input type="color" id="info-color" name="info-color" value="#d61757"
                             />
                        <label for="alert-color">Couleur du Bandeau d'Informations</label>
                    </div>

                    <div>
                        <input type="color" id="info-text" name="info-text" value="#ffffff"
                            />
                        <label for="info-text">Couleur du Texte</label>
                    </div>
                </fieldset>


                <button tabindex="0" class="reset-btn" onclick="resetDefaultStyles()"><i class="fa-solid fa-rotate"></i>
                    Réinitialiser</button>
                <button tabindex="0" class="submit-btn" type="submit" name="submit"
                    onclick="saveChanges()"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
            </form>

        </section>
    </div>
</body>

<script type="text/javascript" src="assets/js/customStyle.js"></script>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
