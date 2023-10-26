//Event listeners
let addSettingButtons = document.querySelectorAll(".add-setting");
for (let i = 0; i<addSettingButtons.length; i++) {
    let button = addSettingButtons[i];
    let settingType = button.getAttribute("name");
    button.addEventListener("click", async () => {
        showSettingModal(settingType);
    })
}

const setLimitValues = async () => {
    let limitRows = document.querySelectorAll(".category-row");
    for (let i = 0; i<limitRows.length; i++){
        let limitButton = limitRows[i].querySelector("button.expense_limit")
        let expenseId = limitButton.getAttribute("id");
        expenseId = expenseId.split('_')[2];
        let limitData = await getLimitForCategory(expenseId);
        console.log(limitData.category_limit);

        let categoryName = limitRows[i].querySelector("input.category-name").getAttribute("value");

        limitButton.addEventListener("click", async () => {
            showLimitModal(categoryName, expenseId,  limitData.category_limit);
        })

        if (limitData.category_limit !== null){
            let limitIcon = limitRows[i].querySelector("button.expense_limit i");
            console.log(limitIcon);
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
        modalName.textContent = settingType;
      });

    $('#settingModal').modal('show');
}
