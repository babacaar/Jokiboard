<!------------HEADER------------>
<?php
$pageTitle = "Calendrier"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
 <div class="gestion page">
  <section class="page-content-calendrier">

    <form action="../controllers/controller_agendas.php" method="post">
     <h1>Préférences d'agenda</h1>
     <hr>

     <label for="url">Lien de l'agenda <span class="required">*</span></label>
     <input type="text" class="form-control" name="url" required style="text-transform: capitalize;">


     <label>Choisissez un agenda <span class="required">*</span></label> <br>



       <div class="radio">
           <input type="radio" name="agendaType" id="agenda-type-google" value='Google' required>
           <label for="agenda-type-google">Agenda Google</label>
       </div>

       <div class="radio">
           <input type="radio" name="agendaType" id="agenda-type-outlook" value='Outlook' required style="text-transform: capitalize;">
           <label for="agenda-type-outlook">Agenda Outlook</label>
       </div>

     <button class="submit-btn" type="submit" class="btn btn-primary">Valider</button>
    </form>
   </div>


  </section>
 </div>
</body>


<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
