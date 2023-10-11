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
            showLimitModal(categoryName, limitData.category_limit);
        })

        if (limitData.category_limit !== null){
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
    };
})
const showLimitModal = (categoryName, limitValue) => {
    $('#limitModal').on('show.bs.modal', function (e) {
        let modal = document.querySelector("#limitModal");
        let modalName = modal.querySelector(".limit-category-name");
        modalName.textContent = categoryName;
        let valueInput = modal.querySelector(".limit-modal-input-number");
        valueInput.setAttribute("value", limitValue);
        let limitModalCheckbox = modal.querySelector("#add-limit");
        if (limitValue !== null) {
            limitModalCheckbox.setAttribute("checked", false);
            modal.querySelector(".limit-modal-input-number").removeAttribute("disabled");
        }
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

setLimitValues();