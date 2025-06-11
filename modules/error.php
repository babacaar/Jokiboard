<h1>Oups !</h1>
<hr>


<p class="message">
    <?php echo $msg; ?>
</p>

<hr>

<a tabindex="0" href="<?php echo isset($_SERVER['HTTPS_REFERER']) ? $_SERVER['HTTPS_REFERER'] : '#'; ?>" class="back-btn">
    <i class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i>Retour
</a>
