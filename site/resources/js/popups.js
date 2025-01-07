 import {ajouterCarteDepuisFormulaire, modifierCarteDepuisFormulaire} from "./api/carte.js";
import {ajouterColonneDepuisFormulaire, modifierColonneDepuisFormulaire} from "./api/colonne.js";
import {ajouterMembreDepuisFormulaire} from "./api/membre.js";

const buttonsAjoutCarte = document.getElementsByClassName('buttonPopupAjoutCarte');
const buttonsModifCarte = document.getElementsByClassName('buttonPopupModifCarte');
const buttonsAjoutColonne = document.getElementsByClassName('buttonPopupAjoutColonne');
const buttonsModifColonne = document.getElementsByClassName('buttonPopupModifColonne');
const buttonAjoutMembre = document.getElementById('buttonPopupAjoutMembre');
const popupsHTML = {}

initializePopups()

function afficherPopupCreationCarte(button) {
    button.insertAdjacentHTML("afterend", popupsHTML['creaCarte-' + button.dataset.idcolonne].outerHTML)
    const popup = button.nextElementSibling
    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            fermerPopup(popup);
        }
    });
    const submitButton = document.getElementById('add_submitButton');
    submitButton.onclick = ajouterCarteDepuisFormulaire
}

function afficherPopupModificationCarte(button) {
    button.insertAdjacentHTML("afterend", popupsHTML['modifCarte-' + button.dataset.idcarte].outerHTML)
    const popup = button.nextElementSibling
    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            fermerPopup(popup);
        }
    });
    const submitButton = document.getElementById('maj_submitButton');
    submitButton.onclick = modifierCarteDepuisFormulaire
}

function afficherPopupCreationColonne(button) {
    button.insertAdjacentHTML("afterend", popupsHTML['creaColonne-' + button.dataset.idtableau].outerHTML)
    const popup = button.nextElementSibling
    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            fermerPopup(popup);
        }
    });
    const submitButton = document.getElementById('add_submitButton');
    submitButton.onclick = ajouterColonneDepuisFormulaire
}

function afficherPopupModificationColonne(button) {
    button.insertAdjacentHTML("afterend", popupsHTML['modifColonne-' + button.dataset.idcolonne].outerHTML)
    const popup = button.nextElementSibling
    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            fermerPopup(popup);
        }
    });
    const submitButton = document.getElementById('majColonne_submitButton');
    submitButton.onclick = modifierColonneDepuisFormulaire
}

function afficherPopupAjoutMembre(button) {
    button.insertAdjacentHTML("afterend", popupsHTML['creaMembre-' + button.dataset.idtableau].outerHTML)
    const popup = button.nextElementSibling
    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            fermerPopup(popup);
        }
    });
    const submitButton = document.getElementById('add_submitButton');
    submitButton.onclick = ajouterMembreDepuisFormulaire
}

function fermerPopup(popup) {
    popup.remove()
}

function fermerPopups() {
    for (const popup of document.getElementsByClassName('popup')) {
        popup.remove()
    }
}

function enregistrerPopup(keyPopup, htmlElementPopup) {
    popupsHTML[keyPopup] = htmlElementPopup
}

function initializePopups() {
    for (const button of buttonsModifCarte) {
        insererNouveauPopupModifCarte(button)
    }
    for (const button of buttonsAjoutCarte) {
        insererNouveauPopupAjoutCarte(button)
    }
    for (const button of buttonsModifColonne) {
        insererNouveauPopupModifColonne(button)
    }
    for (const button of buttonsAjoutColonne) {
        insererNouveauPopupAjoutColonne(button)
    }
    insererNouveauPopupAjoutMembre(buttonAjoutMembre);

}

function insererNouveauPopupAjoutMembre(buttonSpawn) {
    buttonSpawn.onclick = () => afficherPopupAjoutMembre(buttonSpawn);
    const popup = buttonSpawn.nextElementSibling;
    enregistrerPopup('creaMembre-' + buttonSpawn.dataset.idtableau, popup);
    fermerPopup(popup)
}

function insererNouveauPopupModifColonne(buttonSpawn) {
    const popup = buttonSpawn.nextElementSibling
    fermerPopup(popup)
    enregistrerPopup('modifColonne-' + buttonSpawn.dataset.idcolonne, popup)
    buttonSpawn.onclick = () => afficherPopupModificationColonne(buttonSpawn)
}

function insererNouveauPopupAjoutColonne(buttonSpawn) {
    const popup = buttonSpawn.nextElementSibling
    fermerPopup(popup)
    enregistrerPopup('creaColonne-' + buttonSpawn.dataset.idtableau, popup)
    buttonSpawn.onclick = () => afficherPopupCreationColonne(buttonSpawn)
}

function insererNouveauPopupModifCarte(buttonSpawn) {
    const popup = buttonSpawn.nextElementSibling
    fermerPopup(popup)
    enregistrerPopup('modifCarte-' + buttonSpawn.dataset.idcarte, popup)
    buttonSpawn.onclick = () => afficherPopupModificationCarte(buttonSpawn)
}

function insererNouveauPopupAjoutCarte(buttonSpawn) {
    const popup = buttonSpawn.nextElementSibling
    fermerPopup(popup)
    enregistrerPopup('creaCarte-' + buttonSpawn.dataset.idcolonne, popup)
    buttonSpawn.onclick = () => afficherPopupCreationCarte(buttonSpawn)
}

export {
    fermerPopups,
    insererNouveauPopupModifColonne,
    insererNouveauPopupModifCarte,
    insererNouveauPopupAjoutCarte,
    insererNouveauPopupAjoutColonne
}
