let categorySelect = document.querySelector("#expense_category");
categorySelect.addEventListener("change", async () => {
    setLimitValue();
})

const getLimitForCategory = async (categoryId) => {
    try{
        const res = await fetch(`../api/limit/${categoryId}`);
        const data = await res.json();
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}


const setLimitValue = async () => {
    let categorySelect = document.querySelector("#expense_category");
    let categoryId = categorySelect.value;
    let category_limit = await getLimitForCategory(categoryId);
    console.log(category_limit);
    let limitMessageSpan = document.querySelector("#limit-alert-limit-value");
    limitMessageSpan.textContent = category_limit.category_limit;
}

setLimitValue();

let expenseValueInput = document.querySelector("#expense_amount");

expenseValueInput.addEventListener("change", async () => {
    let limitValue = document.querySelector("#limit-alert-limit-value").textContent;
    if (limitValue != ''){
        limitValue = parseFloat(limitValue);
        let expenseValue = parseFloat(expenseValueInput.value);
        if (expenseValue > limitValue){
            console.log("LIMIT EXCEDEED!!!");
        } else {
            console.log("LIMIT OK :) ");
        }
    }
    console.log(limitValue);
    getLimitForMonth();
})

const getLimitForMonth = async () => {
    let expenseDate = document.querySelector("#expense_date");
    console.log(`Expense date: ${expenseDate.value}`);
}