//Event listeners
let addSettingButtons = document.querySelectorAll(".add-setting");
for (let i = 0; i<addSettingButtons.length; i++) {
    let button = addSettingButtons[i];
    let settingType = button.getAttribute("name");
    button.addEventListener("click", async () => {
        showSettingModal(settingType);
    })
}

let settingNameInput = document.querySelector("#modal-setting-name");
settingNameInput.addEventListener("keyup", async () => {
    let name = settingNameInput.value;
    let type = document.querySelector("#modal-setting-type").value;
    let data = await validateName(type, name);
    let submitButton = document.querySelector("#settingModal button[type='submit']")
    let errorLabel = document.querySelector('#formSetting label.error');
    if (data.name_status !== 'false'){
        errorLabel.classList.remove("d-none");
        errorLabel.textContent= data.name_status;
        submitButton.setAttribute("disabled", "");
    } else {
        errorLabel.classList.add("d-none");
        submitButton.removeAttribute("disabled");
    }
})

const setLimitValues = async () => {
    let limitRows = document.querySelectorAll(".category-row");
    for (let i = 0; i<limitRows.length; i++){
        let limitButton = limitRows[i].querySelector("button.expense_limit")
        let expenseId = limitButton.getAttribute("id");
        expenseId = expenseId.split('_')[2];
        let limitData = await getLimitForCategory(expenseId);

        let categoryName = limitRows[i].querySelector("input.category-name").getAttribute("value");

        limitButton.addEventListener("click", async () => {
            showLimitModal(categoryName, expenseId,  limitData.category_limit);
        })

        if (limitData.category_limit !== null){
            let limitIcon = limitRows[i].querySelector("button.expense_limit i");
            limitIcon.classList.remove("icon-lock-open");
            limitIcon.classList.add("icon-lock");
            let limitValue = limitRows[i].querySelector("span.limit-value");
            limitValue.textContent = limitData.category_limit;
            let limitLabel = limitRows[i].querySelector("label.expense_limit");
            limitLabel.removeAttribute("hidden");
        }


    }

}

let limitModalCheckbox = document.querySelector("#add-limit");
limitModalCheckbox.addEventListener("click", async () => {
    if (limitModalCheckbox.checked === true){
        document.querySelector(".limit-modal-input-number").removeAttribute("disabled");
    } else {
        document.querySelector(".limit-modal-input-number").setAttribute("disabled", "");
        let form = document.querySelector("#formLimit");
        let formAction = form.getAttribute("action");
        formAction = formAction.split('&')[0];
        form.setAttribute("action", `${formAction}&null`);
    };
})

const showLimitModal = (categoryName, categoryId, limitValue) => {
    $('#limitModal').on('show.bs.modal', function (e) {
        let modal = document.querySelector("#limitModal");
        let modalName = modal.querySelector(".limit-category-name");
        modalName.textContent = categoryName;
        let valueInput = modal.querySelector(".limit-modal-input-number");
        valueInput.setAttribute("value", limitValue);
        let form = modal.querySelector("#formLimit");
        let limitModalCheckbox = modal.querySelector("#add-limit");
        if (limitValue !== null) {
            limitModalCheckbox.setAttribute("checked", false);
            modal.querySelector(".limit-modal-input-number").removeAttribute("disabled");
        }
        form.setAttribute("action", `/api/setLimit/${categoryId}&${limitValue}`)
      });

    $('#limitModal').modal('show');
}

const closeLimitModal = () => $('#limitModal').modal('hide');

const getLimitForCategory = async (categoryId) => {
    try{
        const res = await fetch(`../api/limit/${categoryId}`);
        const data = await res.json();
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}

const saveLimit = () => {
    let limitModalCheckbox = document.querySelector("#add-limit");
    let limitValue = null;
    if (limitModalCheckbox.checked === true){

    }

}
setLimitValues();


let valueInput = document.querySelector(".limit-modal-input-number");
valueInput.addEventListener("change", async (e) => {
    let modal = document.querySelector("#limitModal");
    limitValue = valueInput.value;
    if (limitValue === ''){
        limitValue = null;
    }
    let form = modal.querySelector("#formLimit");
    let formAction = form.getAttribute("action");
    formAction = formAction.split('&')[0];
    form.setAttribute("action", `${formAction}&${limitValue}`)
})

const showSettingModal = (settingType) => {
    $('#settingModal').on('show.bs.modal', function (e) {
        let modal = document.querySelector("#settingModal");
        let modalName = modal.querySelector(".modal-setting-type");
        let typeInput = modal.querySelector("#modal-setting-type");
        let settingName = modal.querySelector("#modal-setting-name");
        let errorLabel = modal.querySelector('#formSetting label.error');
        errorLabel.classList.add("d-none");
        settingName.value = "";
        typeInput.value = settingType;
        modalName.textContent = setSettingName(settingType);
        let limitDiv = modal.querySelector(".setting-limit");
        if (settingType === "expense"){
            limitDiv.classList.remove("d-none");
        } else {
            limitDiv.classList.add("d-none");
        }
        let form = modal.querySelector("#formSetting");
        form.setAttribute("action", `/settings/add/${settingType}`);
      });

    $('#settingModal').modal('show');
}

const setSettingName = (settingType) => {
    switch(settingType){
        case "income":
            return "źródło dochodu";
        case "expense":
            return "kategorię wydatku";
        case "payment":
            return "sposób płatności";
        default:
            return "";
    }
}

const capitalizeFirstLetter = (string) =>{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

const closeSettingModal = () => $('#settingModal').modal('hide');

let settingLimitCheckbox = document.querySelector("#setting-add-limit");
settingLimitCheckbox.addEventListener("click", () =>{
    let settingLimitValue = document.querySelector("#setting-limitValue");
    if (settingLimitCheckbox.checked === true){
        settingLimitValue.removeAttribute("disabled");
    } else {
        settingLimitValue.setAttribute("disabled", "");
    }
})

const validateName = async (type, name, id=0) => {
    try{
        let status = "";
        let data = "";
        if (name == "") {
            status = '{"name_status":"Nazwa nie może być pusta"}';
            data = JSON.parse(status);
        } else {
            const res = await fetch(`../api/settings/vaidate/${type}/${id}/${name}`);
            data = await res.json();
        }
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}