const html = `
<div id="popup_changement_mdp">
    <p>
        <label for="new_mdp_id">Nouveau mot de passe&#42;</label>
        <label for="new_mdp_id"><b>6 à 50 caractères, au moins une minusle, une majuscule et un caractère spécial</b></label>
        <input type="password" value="" placeholder="" minlength="6" maxlength="50" autocomplete="new-password"
                name="new_mdp" id="new_mdp_id" required>
    </p>
    <p>
        <label for="new_mdp2_id">Vérification du nouveau mot de passe&#42;</label>
        <input type="password" value="" placeholder="" minlength="6" maxlength="50" autocomplete="off"
                name="new_mdp2" id="new_mdp2_id" required>
    </p>
</div>
`

function afficherPopupChangementMdp(button) {
    button.insertAdjacentHTML("afterend", html)
    button.innerText = 'Ne pas changer de mot de passe'
    button.onclick = () => fermerPopupChangementMdp(button)
}

function fermerPopupChangementMdp(button) {
    document.getElementById('popup_changement_mdp').remove()
    button.innerText = 'Changer de mot de passe'
    button.onclick = () => afficherPopupChangementMdp(button)
}