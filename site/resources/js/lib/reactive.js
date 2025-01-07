let objectByName = new Map();
let registeringEffect = null;
let objectDependencies = new Map();

function applyAndRegister(effect) {
    registeringEffect = effect;
    effect();
    registeringEffect = null;
}

function registerEffect(target, key) {
    if (!objectDependencies.get(target).has(key))
        objectDependencies.get(target).set(key, new Set());
    objectDependencies.get(target).get(key).add(registeringEffect);
}

function trigger(target, key) {
    if (!objectDependencies.get(target).has(key)) return;

    for (const effect of objectDependencies.get(target).get(key)) {
        effect();
    }
}

function reactive(passiveObject, name) {
    objectDependencies.set(passiveObject, new Map());

    const handler = {
        get(target, key) {
            if (registeringEffect !== null)
                registerEffect(target, key);
            return target[key];
        },
        set(obj, field, value) {
            obj[field] = value;
            trigger(obj, field);
            return true;
        }
    };

    let reactiveObject = new Proxy(passiveObject, handler);

    objectByName.set(name, reactiveObject);

    return reactiveObject;
}

function startReactiveDom(scope = document) {
    const elementsClickables = scope.querySelectorAll("[data-onclick]");
    const elementsFunctionnables = scope.querySelectorAll("[data-textfun]");
    const elementsVariables = scope.querySelectorAll("[data-textvar]");
    const elementsStyles = scope.querySelectorAll("[data-stylefun]");
    const elementsHTMLFunctionnables = scope.querySelectorAll("[data-htmlfun]")
    const elementsHTMLVariables = scope.querySelectorAll("[data-htmlvar]")
    
    for (let elementClickable of elementsClickables) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            objet[methode](argument);
        })
    }
    for (let elementFunctionnable of elementsFunctionnables) {
        const [nomObjet, methode, argument] = elementFunctionnable.dataset.textfun.split(/[.()]+/);
        applyAndRegister(() => elementFunctionnable.innerText = objectByName.get(nomObjet)[methode](argument));
    }
    for (let elementVariable of elementsVariables) {
        const [nomObjet, attribut] = elementVariable.dataset.textvar.split('.');
        applyAndRegister(() => elementVariable.textContent = objectByName.get(nomObjet)[attribut]);
    }
    for (let elementStyle of elementsStyles) {
        const [nomObjet, methode, argument] = elementStyle.dataset.stylefun.split(/[.()]+/);
        applyAndRegister(() => {
            const obj = objectByName.get(nomObjet)[methode](argument);
            for (const prop in obj) {
                elementStyle.style[prop] = obj[prop];
            }
        });
    }
    for (let elementHTMLFunctionnable of elementsHTMLFunctionnables) {
        const [nomObjet, methode, argument] = elementHTMLFunctionnable.dataset.htmlfun.split(/[.()]+/);
        applyAndRegister(() => {
            elementHTMLFunctionnable.innerHTML = objectByName.get(nomObjet)[methode](argument);
            startReactiveDom(elementHTMLFunctionnable);
        });
    }
    for (let elementHTMLVariable of elementsHTMLVariables) {
        const [nomObjet, attribut] = elementHTMLVariable.dataset.htmlvar.split('.');
        applyAndRegister(() => {
            elementHTMLVariable.innerHTML = objectByName.get(nomObjet)[attribut];
            startReactiveDom(elementHTMLVariable);
        });
    }
}

export {reactive, startReactiveDom, applyAndRegister};
