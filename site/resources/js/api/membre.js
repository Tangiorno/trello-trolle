import {removeAffectationReactive} from "./carte.js";
import {fermerPopups} from "../popups.js";
import {startReactiveDom} from "../lib/reactive.js";

const buttonDeleteMember = document.getElementsByClassName('delete-membre');

function supprimerMembre(button) {
    let codeTableau = button.dataset.idTableau;
    let login = button.dataset.idMembre;

    let URL = apiBase + "membre/" + codeTableau + "/supprimer/" + login;

    const xhr = new XMLHttpRequest();
    xhr.open("DELETE", URL, true);
    xhr.addEventListener("load", function () {
        if (xhr.status === 200) {
            const divMembre = button.closest("div.membres");
            divMembre.remove();

            const rep = JSON.parse(xhr.responseText)
            for (const idCarte of rep[2]) {
                removeAffectationReactive(idCarte, `<span>${rep[0]} ${rep[1]}</span>`)
            }
        }
    });
    xhr.send();
}

function ajouterMembreDepuisFormulaire() {
    const idTableau = document.getElementById('addMember_idTableau').value;
    const login = document.getElementById("add_loginMembre").value;

    fermerPopups()

    const xhr = new XMLHttpRequest();
    xhr.open('PATCH', `${apiBase}membre`, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');

    xhr.addEventListener("load", function () {
        let backData = JSON.parse(xhr.responseText);

        const button = document.getElementById('buttonPopupAjoutMembre')

        button.insertAdjacentHTML('beforebegin', templateMembre(backData));

        const membre = button.previousElementSibling;

        initListenersDeletionMembers()

        startReactiveDom(membre);
    })

    xhr.send(JSON.stringify({
        idTableau: idTableau,
        membre: login
    }));
}

initListenersDeletionMembers()

function initListenersDeletionMembers() {
    for (const button of buttonDeleteMember) {
        button.addEventListener('click', function () {
            supprimerMembre(button);
        });
    }
}

function templateMembre(backData) {
    return `
            <div class="membres">
                <li>
                    <div class="icons_menu_stick">
                        ${backData.membre.prenom} ${backData.membre.nom}
                        <span class="actions">
                            <button class="delete-membre" data-id-membre="${backData.membre.login}"
                                    data-id-tableau="${backData.codeTableau}"><img
                                    class="icon" src="../../resources/img/x.png"
                                    alt="Retirer le membre"></button>
                        </span>
                    </div>
                </li>
            </div>`;
}

export {ajouterMembreDepuisFormulaire}