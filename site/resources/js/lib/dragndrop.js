let carte;

for (const colonne of document.getElementsByClassName("colonne")) {
    if (colonne.dataset.droppable === "true") {
        colonne.addEventListener('dragover', allowDrop);
        colonne.addEventListener("drop", drop);
    }
}

for (const carte of document.getElementsByClassName("carte")) {
    if (carte.dataset.draggable === "true") {
        carte.addEventListener('dragstart', drag);
        carte.draggable = "true";
    }
}

function allowDrop(event) {
    event.preventDefault();
}

function drag(event) {
    carte = event.target;
}

function drop(event) {
    const colonne = event.target.closest('.colonne');
    colonne.appendChild(carte);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../../AJAX/dragndrop.php?carte=' + carte.id.slice(carte.id.indexOf('-') + 1) + '&colonne=' + colonne.id.slice(colonne.id.indexOf('-') + 1));
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('drag&drop OK');
        } else {
            console.error('Fail');
        }
    };
    xhr.send();
}
