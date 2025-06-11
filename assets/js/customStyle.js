// Styles par défaut
var defaultStyles = {
  primaryColor: "#ff721f",
  secondaryColor: "#0099dc",
  alertColor: "#d61757",
  alertTextColor: "#ffffff",
  infoColor: "#d61757",
  infoTextColor: "#ffffff",
  backgroundColor: "#ff721f",
  alertBlink: true,
  // L'url de l'arrière plan est stockée dans une variable css
  backgroundImageUrl: "../images/BGv2.png",
  logoUrl: "../images/LOGOv1.png",
};

// Éléments du formulaire
var formElements = {
  primaryColor: document.getElementById("primary"),
  secondaryColor: document.getElementById("secondary"),
  alertColor: document.getElementById("alert-color"),
  alertTextColor: document.getElementById("alert-text"),
  infoColor: document.getElementById("info-color"),
  infoTextColor: document.getElementById("info-text"),
  backgroundColor: document.getElementById("background-color"),
  backgroundImageUrl: document.getElementById("background-image-url"),
  logoUrl: document.getElementById("logo-url"),
  alertBlink: document.getElementById("alert-blink-checkbox"),
};

// Éléments du DOM à mettre à jour
var domElements = {
  alertBanner: document.getElementById("alert-banner"),
  logoImg: document.getElementById("logo-img"),
  alertPreview: document.getElementById("alert-preview"),
  alertPreviewMessage: document.getElementById("alert-preview-message"),
};

var lightPercentage = 60;

// Appliquer les couleurs au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  applyStoredStyles();
  updateAlertPreview();
});

// Récupérer les éléments du formulaire et détecter les changements
updateFormElementColor(formElements.primaryColor, "primaryColor");
updateFormElementColor(formElements.secondaryColor, "secondaryColor");
updateFormElementColor(formElements.alertColor, "alertColor");
updateFormElementColor(formElements.infoColor, "infoColor");

updateFormElementText(formElements.alertTextColor, "alertColor");
updateFormElementText(formElements.infoTextColor, "infoColor");

if (formElements.backgroundColor) formElements.backgroundColor.addEventListener("change", updateBackgroundColor);
if (formElements.backgroundImageUrl) formElements.backgroundImageUrl.addEventListener("input", updateBackgroundImage);
if (formElements.logoUrl) formElements.logoUrl.addEventListener("input", updateLogo);
if (formElements.alertBlink)
  formElements.alertBlink.addEventListener("change", (e) => {
    updateAlertBlink(e);
    updateAlertPreview();
  });

// Changer les styles dans le CSS
function updateColor(property, value, lighter = true, text = false) {
  property = extractColorName(property);

  const propertyName = text ? `--text-${property}` : `--${property}`;
  document.documentElement.style.setProperty(propertyName, value);

  if (lighter) {
    document.documentElement.style.setProperty(`--light-${property}`, getLighterColor(value));
  }
}

function extractColorName(propertyName) {
  // Supprime le suffixe "Color" ou "Light" de la propriété
  return propertyName.replace(/Color|Light|Text/g, "");
}

// Détecter changements de couleur dans le formulaire
function updateFormElementColor(element, property) {
  if (element) {
    element.addEventListener("change", () => {
      if (element.id.includes("alert")) updateAlertPreview();
      updateColor(property, element.value);
    });
  }
}

// Détecter changements de texte dans le formulaire
function updateFormElementText(element, property) {
  if (element) {
    element.addEventListener("change", () => {
      if (element.id.includes("alert")) updateAlertPreview();
      updateColor(property, element.value, false, true);
    });
  }
}

// Détecter changements de couleur d'arrière plan dans le formulaire
function updateBackgroundColor() {
  if (formElements.backgroundColor) {
    updateColor("background", formElements.backgroundColor.value, false);
  }
}

function updateBackgroundImage(e) {
  const imageUrl = e.target.value.trim(); // Trim pour supprimer les espaces blancs

  // Vérifier si l'URL est vide ou non
  const backgroundImageValue = imageUrl ? formatImageUrl(imageUrl) : formatImageUrl("");

  updateColor("bg-image-url", backgroundImageValue, false);
}

function updateLogo(e) {
  domElements.logoImg.src = e.target.value;
}

function updateAlertBlink(e) {
  toggleBlinkClass(e.target.checked);
}

function toggleBlinkClass(checked) {
  const { alertBanner } = domElements;

  if (alertBanner) {
    if (checked === "true") {
      alertBanner.classList.add("blink");
    } else {
      alertBanner.classList.remove("blink");
    }
  }
}

function formatImageUrl(url) {
  // Concaténation pour garder les valeurs de linear-gradient
  return "linear-gradient(var(--transparent-light), var(--transparent-light)), url('" + url + "')";
}

function extractImageUrl(fullUrl) {
  return fullUrl.split("'")[1]; // Récupérer seulement la partie URL
}

function getLighterColor(hex) {
  // Convertir la couleur hexadécimale en valeurs RGB
  var r = parseInt(hex.substring(1, 3), 16);
  var g = parseInt(hex.substring(3, 5), 16);
  var b = parseInt(hex.substring(5, 7), 16);

  // Calculer la différence vers le blanc pour chaque composante de couleur
  var differenceR = (255 - r) * (lightPercentage / 100);
  var differenceG = (255 - g) * (lightPercentage / 100);
  var differenceB = (255 - b) * (lightPercentage / 100);

  // Ajouter la différence à chaque composante de couleur
  r = Math.min(255, Math.round(r + differenceR));
  g = Math.min(255, Math.round(g + differenceG));
  b = Math.min(255, Math.round(b + differenceB));

  // Convertir les valeurs RGB en couleur hexadécimale
  var newColor = "#" + ((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1);

  return newColor;
}

// Réinitialiser aux valeurs par défaut
function resetDefaultStyles() {
  Object.keys(defaultStyles).forEach((key) => {
    if (key === "backgroundImageUrl") {
      formElements.backgroundImageUrl.value = defaultStyles[key];
    } else if (key === "logoUrl") {
      formElements.logoUrl.value = defaultStyles[key];
    } else if (key === "alertBlink") {
      toggleBlinkClass(defaultStyles[key]);
    } else {
      updateColor(key, defaultStyles[key]);
      if (formElements[key]) formElements[key].value = defaultStyles[key];
    }

    localStorage.setItem(key, defaultStyles[key]);
  });
}

function applyStoredStyles() {
  // console.log(localStorage);

  Object.keys(defaultStyles).forEach((key) => {
    const storedValue = localStorage.getItem(key);

    if (key === "backgroundImageUrl") {
      document.documentElement.style.setProperty("--bg-image-url", formatImageUrl(storedValue));
      if (formElements.backgroundImageUrl) formElements.backgroundImageUrl.value = storedValue;
    } else if (key === "logoUrl") {
      domElements.logoImg.src = storedValue;
      if (formElements.logoUrl) formElements.logoUrl.value = storedValue;
    } else if (key === "alertBlink") {
      toggleBlinkClass(storedValue);
      if (formElements.alertBlink) formElements.alertBlink.checked = storedValue === "true";
    } else {
      if (key.includes("Text")) {
        updateColor(key, storedValue, false, true);
      } else updateColor(key, storedValue);
      if (formElements[key]) formElements[key].value = storedValue;
    }
  });
}

// Sauvegarder les changements dans le localStorage
function saveChanges() {
  Object.keys(formElements).forEach((key) => {
    if (key === "alertBlink") localStorage.setItem(key, formElements[key].checked);
    else localStorage.setItem(key, formElements[key].value);
  });
}

var alertOverviewCheck = document.getElementById("alert-overview");

if (alertOverviewCheck)
  alertOverviewCheck.addEventListener("change", (e) => {
    if (e.target.checked) {
      domElements.alertPreview.style.display = "flex";
    } else {
      domElements.alertPreview.style.display = "none";
    }
  });

// Mettre à jour l'aperçu du bandeau d'alerte dans le formulaire
function updateAlertPreview() {
  if (domElements.alertPreview && domElements.alertPreviewMessage) {
    domElements.alertPreview.style.backgroundColor = formElements.alertColor.value;
    domElements.alertPreviewMessage.style.color = formElements.alertTextColor.value;

    if (formElements.alertBlink.checked) {
      domElements.alertPreview.classList.add("blink");
    } else {
      domElements.alertPreview.classList.remove("blink");
    }
  }
}
