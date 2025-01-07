import {decodeHtmlSpecialChars} from "../lib/utility.js";
import {reactive, startReactiveDom} from "../lib/reactive.js";
import {fermerPopups, insererNouveauPopupAjoutCarte, insererNouveauPopupModifColonne} from "../popups.js";

function creerColonneReactive(id, titre) {
    window['colonne-' + id] = reactive({
        titre: decodeHtmlSpecialChars(titre)
    }, "colonne-" + id);
}

function changerTitreColonneReactif(id, titre) {
    window['colonne-' + id].titre = titre;
}

function supprimerColonne(button) {
    let idColonne = button.dataset.idColonne;
    let URL = apiBase + "colonne/" + idColonne;
    fetch(URL, {method: "DELETE"})
        .then(response => {
            if (response.status === 200) {
                let divColonne = button.closest("div.colonne");
                divColonne.remove();
            }
        });
}

function modifierColonneDepuisFormulaire() {
    const idColonne = document.getElementById('majColonne_idColonne').value;
    const titreColonne = document.getElementById('majColonne_titreColonne').value;

    fermerPopups()

    changerTitreColonneReactif(idColonne, titreColonne);

    const xhr = new XMLHttpRequest();
    let URL = apiBase + "colonne/" + idColonne;
    xhr.open('PATCH', URL, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');

    const jsonData = JSON.stringify({
        "titreColonne": titreColonne
    });

    xhr.send(jsonData);
}

//  *** Ajouter une colonne ***

function ajouterColonneDepuisFormulaire() {
    const nomColonne = document.getElementById('add_nomColonne').value;
    const idTableau = document.getElementById('add_idTableau').value;

    fermerPopups()

    const xhr = new XMLHttpRequest();
    xhr.open('POST', `${apiBase}colonne`, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.addEventListener("load", function () {
        let backData = JSON.parse(xhr.responseText);
        
        creerColonneReactive(backData.colonne.idColonne, nomColonne);

        //const tableau = document.getElementById(`tableau-${backData.colonne.tableau.idTableau}`);
        document.getElementsByClassName('buttonPopupAjoutColonne')[0].insertAdjacentHTML('beforebegin', templateColonne(backData));

        const colonne = document.getElementById('colonne-' + backData.colonne.idColonne)

        insererNouveauPopupModifColonne(colonne.getElementsByClassName('buttonPopupModifColonne')[0])
        insererNouveauPopupAjoutCarte(colonne.getElementsByClassName('buttonPopupAjoutCarte')[0])

        initEventListenersDeletionColonne()
        
        startReactiveDom(colonne)
    })

    xhr.send(JSON.stringify({
        titre: nomColonne, idTableau: idTableau
    }));
}

initEventListenersDeletionColonne()

function initEventListenersDeletionColonne() {
    const buttonDeleteColonne = document.getElementsByClassName('delete-colonne');
    for (const button of buttonDeleteColonne) {
        button.addEventListener('click', function () {
            supprimerColonne(button);
        });
    }
}

function templateColonne(backData) {
    let template = `
        <div class="colonne" id="colonne-${backData.colonne.idColonne}"`;

    if (backData.estCoEtParticipe) template += `ondrop="drop(event)" ondragover="allowDrop(event)"`;

    template += `>
        <div class="titre icons_menu">
            <span data-textvar="colonne-${backData.colonne.idColonne}.titre"></span>`;

    if (backData.estCoEtParticipe) template += `
            <span class="actions">
                <button class="buttonPopupModifColonne" data-idcolonne="${backData.colonne.idColonne}">
                    <img class="icon" src="../../resources/img/editer.png" alt="Éditer la colonne">
                </button>
                <div class="popup">
                    <form>
                        <fieldset>
                            <h3>Modification d'une colonne :</h3>
                            <p>
                                <label for="majColonne_titreColonne">Nom de la colonne&#42;</label> :
                                <input type="text" placeholder="KO" name="titreColonne" id="majColonne_titreColonne" minlength="1" maxlength="50"
                                       value='${backData.colonne.titreColonne}' required>
                            </p>
                            <input type='hidden' id='majColonne_idColonne' value='${backData.colonne.idColonne}'>
                            <p>
                                <input type="button" id="majColonne_submitButton" value="Mettre à jour la colonne">
                            </p>
                        </fieldset>
                    </form>
                </div>
                <button class="delete-colonne" data-id-colonne="${backData.colonne.idColonne}">
                    <img class="icon" src="../../resources/img/x.png" alt="Supprimer la colonne">
                </button>
            </span>
        `;

    template += `</div>
        <div class="corps" data-tableau="${backData.colonne.tableau.idTableau}">`;

    if (backData.estCoEtParticipe) template += `
            <button class="ajout-tableau buttonPopupAjoutCarte"
                    data-idColonne="${backData.colonne.idColonne}">
                <span class="titre icons_menu btn-ajout">Ajouter une carte</span>
            </button>
            <div class="popup">
                <form>
                    <fieldset>
                        <h3>Création d'une carte :</h3>
                        <p>
                            <label for="add_titreCarte">Titre de la carte&#42;</label> :
                            <input type="text" placeholder="Ma super tâche" name="titreCarte"
                                   id="add_titreCarte" minlength="1"
                                   maxlength="50" required>
                        </p>
                        <p>
                            <label for="add_descriptifCarte">Description de la carte&#42;</label> :
                        <div>
                            <textarea placeholder="Description de la tâche..."
                                      name="descriptifCarte" id="add_descriptifCarte"
                                      required></textarea>
                        </div>
                        <p>
                            <label for="add_couleurCarte">Couleur de la carte&#42;</label> :
                            <input type="color" value="#FFFFFF" name="couleurCarte"
                                   id="add_couleurCarte" required>
                        </p>
                        <p>
                            <label for="add_affectationsCarte">Membres affectés :</label>
                        <div>
                            <select multiple name="affectationsCarte[]" id="add_affectationsCarte">
                                <option value="${backData.colonne.tableau.proprietaire.login}" data-membretostring="${backData.colonne.tableau.proprietaire.prenom} ${backData.colonne.tableau.proprietaire.nom}">
                                    ${backData.colonne.tableau.proprietaire.prenom}
                                    ${backData.colonne.tableau.proprietaire.nom}
                                    ${backData.colonne.tableau.proprietaire.login}
                                </option>`;

    for (const membre of backData.colonne.tableau.participants) template += `
            <option value="${membre.login}" data-membretostring="${membre.prenom} ${membre.nom}">
                ${membre.prenom}
                ${membre.nom}
                ${membre.login}
            </option>`;

    template += `</select>
                        </div>
                        <input type="hidden" id="add_idColonne" name="idColonne" value="${backData.colonne.idColonne}">
                        <p>
                            <input type="button" id="add_submitButton" value="Créer la carte">
                        </p>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    `;

    return template;
}

export {modifierColonneDepuisFormulaire, creerColonneReactive, ajouterColonneDepuisFormulaire}
