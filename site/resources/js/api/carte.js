import {decodeHtmlSpecialChars} from "../lib/utility.js";
import {reactive, startReactiveDom} from "../lib/reactive.js";
import {fermerPopups, insererNouveauPopupModifCarte} from "../popups.js";

function creerCarteReactive(id, titre, couleur, description, affectations) {
    window['carte-' + id] = reactive({
        titre: decodeHtmlSpecialChars(titre),
        couleur: decodeHtmlSpecialChars(couleur),
        description: decodeHtmlSpecialChars(description),
        affectations: decodeHtmlSpecialChars(affectations),
        getStyle: function () {
            return {
                backgroundColor: this.couleur,
            };
        },
    }, "carte-" + id)
}

function removeAffectationReactive(idCarte, affectationHTMLtoRemove) {
    window['carte-' + idCarte].affectations = window['carte-' + idCarte].affectations.replace(affectationHTMLtoRemove, '')
}

/**
 * *** Supprimer une carte ***
 * @param {HTMLElement} button La balise <button> cliquée
 */
function supprimerCarte(button) {
    let idCarte = button.dataset.idCarte;
    let URL = apiBase + "carte/" + idCarte;

    fetch(URL, {method: "DELETE"})
        .then(response => {
            if (response.status === 200) {
                // Plus proche ancêtre <div class="feedy">
                let divCarte = button.closest("div.carte");
                divCarte.remove();
            }
        });
}

function modifierCarteDepuisFormulaire() {
    const idCarte = document.getElementById('maj_idCarte').value
    const titreCarte = document.getElementById('maj_titreCarte').value
    const descriptifCarte = document.getElementById('maj_descriptifCarte').value
    const couleurCarte = document.getElementById('maj_couleurCarte').value
    const idColonne = document.getElementById('maj_idColonne').value

    const affectationsCarteJS = Array.prototype.slice.call(document.querySelectorAll('#maj_affectationsCarte option:checked'), 0).map(function (v) {
        return v.dataset.membretostring;
    });
    const affectationsCartePHP = Array.prototype.slice.call(document.querySelectorAll('#maj_affectationsCarte option:checked'), 0).map(function (v) {
        return v.value;
    });
    let affectationsHTML = ""
    for (const aff of affectationsCarteJS) {
        affectationsHTML += '<span>' + aff + '</span>'
    }

    fermerPopups()

    let carteReactive = window['carte-' + idCarte]

    carteReactive.titre = titreCarte
    carteReactive.description = descriptifCarte
    carteReactive.couleur = couleurCarte
    carteReactive.affectations = affectationsHTML

    let URL = apiBase + "carte/" + idCarte;
    const xhr = new XMLHttpRequest();
    xhr.open('PATCH', URL, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');

    const jsonData = JSON.stringify({
        "idColonne": idColonne,
        "titreCarte": titreCarte,
        "descriptifCarte": descriptifCarte,
        "couleurCarte": couleurCarte,
        "affectationsCarte": affectationsCartePHP
    });

    xhr.send(jsonData);
}

function ajouterCarteDepuisFormulaire() {
    const titreCarte = document.getElementById('add_titreCarte').value
    const descriptifCarte = document.getElementById('add_descriptifCarte').value
    const couleurCarte = document.getElementById('add_couleurCarte').value
    const idColonne = document.getElementById('add_idColonne').value

    const affectationsCarteJS = Array.prototype.slice.call(document.querySelectorAll('#add_affectationsCarte option:checked'), 0).map(function (v) {
        return v.dataset.membretostring;
    });
    const affectationsCartePHP = Array.prototype.slice.call(document.querySelectorAll('#add_affectationsCarte option:checked'), 0).map(function (v) {
        return v.value;
    });
    let affectationsHTML = ""
    for (const aff of affectationsCarteJS) {
        affectationsHTML += '<span>' + aff + '</span>'
    }

    fermerPopups()

    const xhr = new XMLHttpRequest();
    xhr.open('POST', `${apiBase}carte`, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.addEventListener("load", function () {
        let infosBack = JSON.parse(xhr.responseText);

        const colonne = document.getElementById(`colonne-${infosBack.carte.colonne.idColonne}`);
        colonne.getElementsByClassName('buttonPopupAjoutCarte')[0].insertAdjacentHTML('beforebegin', templateCarte(infosBack));

        const carte = document.getElementById('carte-' + infosBack.carte.idCarte)

        insererNouveauPopupModifCarte(carte.getElementsByClassName('buttonPopupModifCarte')[0])

        creerCarteReactive(infosBack.carte.idCarte, titreCarte, couleurCarte, descriptifCarte, affectationsHTML)
        
        initEventListenerDeletionCarte()

        startReactiveDom()
    })

    xhr.send(JSON.stringify({
        idColonne: idColonne,
        titre: titreCarte,
        descriptif: descriptifCarte,
        couleur: couleurCarte,
        affectation: affectationsCartePHP
    }));
}

initEventListenerDeletionCarte()
function initEventListenerDeletionCarte() {
    const buttonDeleteCarte = document.getElementsByClassName('delete-carte');
    for (const button of buttonDeleteCarte) {
        button.addEventListener('click', function () {
            supprimerCarte(button);
        });
    }
}

function templateCarte(infosBack) {
    let template = `<div class="carte"
             id="carte-${infosBack.carte.idCarte}" `;

    if (infosBack.estCoEtParticipe) template += ` draggable="true" ondragstart="drag(event)" `;

    template += `data-stylefun="carte-${infosBack.carte.idCarte}.getStyle()">
            <div class="titre icons_menu">
                <span data-textvar="carte-${infosBack.carte.idCarte}.titre"></span>`;

    if (infosBack.estCoEtParticipe) {
        template += `<span class="actions">
                        <button class="buttonPopupModifCarte" data-idCarte="${infosBack.carte.idCarte}">
                            <img class="icon" src="../../resources/img/editer.png" alt="Éditer la carte">
                        </button>
                        <div class="popup">
                            <form>
                                <fieldset>
                                    <h3>Modification d'une carte :</h3>
                                    <p>
                                        <label for="maj_titreCarte">Titre de la carte&#42;</label> :
                                        <input type="text" placeholder="Ma super tâche" name="titreCarte" id="maj_titreCarte"
                                               value="${infosBack.carte.titreCarte}" minlength="1" maxlength="50" required>
                                    </p>
                                    <p>
                                        <label for="maj_descriptifCarte">Description de la carte&#42;</label> :
                                    <div>
                                        <textarea placeholder="Description de la tâche..." name="descriptifCarte" id="maj_descriptifCarte"
                                                  required >${infosBack.carte.descriptifCarte}</textarea>
                                    </div>
                                    <p>
                                        <label for="maj_couleurCarte">Couleur de la carte&#42;</label> :
                                        <input type="color" value="${infosBack.carte.couleurCarte}" name="couleurCarte" id="maj_couleurCarte"
                                               required>
                                    </p>
                                    <p>
                                        <label for="maj_affectationsCarte">Membres affectés :</label>
                                    <div>
                                        <select multiple name="affectationsCarte[]" id="maj_affectationsCarte">`;

        const proprietaire = infosBack.carte.colonne.tableau.proprietaire;
        const loginsAffectes = infosBack.carte.affectationsCarte.map(u => u.login);

        if (proprietaire.login in loginsAffectes) template += `<option selected value="${proprietaire.login}" data-membretostring="${proprietaire.prenom} ${proprietaire.nom}">
                ${proprietaire.prenom}
                ${proprietaire.nom}
                ${proprietaire.login}
            </option>`; else template += `<option value="${proprietaire.login}" data-membretostring="${proprietaire.prenom} ${proprietaire.nom}">
                ${proprietaire.prenom}
                ${proprietaire.nom}
                ${proprietaire.login}
            </option>`;

        const participants = infosBack.carte.colonne.tableau.participants;

        for (const membre of participants) {
            if (loginsAffectes.includes(membre.login)) template += `
                <option selected value="${membre.login}" data-membretostring="${membre.prenom} ${membre.nom}">
                    ${membre.prenom}
                    ${membre.nom}
                    ${membre.login}
                </option>`; else template += `
                <option value="${membre.login}" data-membretostring="${membre.prenom} ${membre.nom}">
                    ${membre.prenom}
                    ${membre.nom}
                    ${membre.login}
                </option>`;
        }

        template += `
                                        </select>
                                    </div>
                                    <input type='hidden' id="maj_idCarte" value='${infosBack.carte.idCarte}'>
                                    <input type='hidden' id="maj_idColonne" value='${infosBack.carte.colonne.idColonne}'>
                                    <p>
                                        <input type="button" id="maj_submitButton" value="Mettre à jour la carte">
                                    </p>
                                </fieldset>
                            </form>
                        </div>

                        <button class="delete-carte" data-id-carte="${infosBack.carte.idCarte}">
                            <img class="icon" src="../../resources/img/x.png" alt="Supprimer la carte">
                        </button>
                    </span>`;
    }

    template += `</div>
            <div class="corps" data-textvar="carte-${infosBack.carte.idCarte}.description"></div>
            <div class="pied" data-htmlvar="carte-${infosBack.carte.idCarte}.affectations"></div>
        </div>`;

    return template;
}

export {creerCarteReactive, modifierCarteDepuisFormulaire, ajouterCarteDepuisFormulaire, removeAffectationReactive}
