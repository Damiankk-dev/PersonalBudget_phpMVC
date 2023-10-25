//Event Listeners
    //Dropdown
let categorySelect = document.querySelector("#expense_category");
categorySelect.addEventListener("change", async () => {
    setLimitValue();
    await setStatusOfExpense();
    await showExpenseStatus();
})
    //Value input
let expenseValueInput = document.querySelector("#expense_amount");
expenseValueInput.addEventListener("change", async () => {
    await setStatusOfExpense();
    await showExpenseStatus();
})
    //Datepicker
let expenseDate = document.querySelector("#expense_date");
expenseDate.addEventListener("change", async () => {
    await setStatusOfExpense();
    await showExpenseStatus();
})

//API queries
const getLimitForCategory = async () => {
    let categorySelect = document.querySelector("#expense_category");
    let categoryId = categorySelect.value;
    try{
        const res = await fetch(`../api/limit/${categoryId}`);
        const data = await res.json();
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}

const getMonthlySumOfExpensesForCategory = async () => {
    let categorySelect = document.querySelector("#expense_category");
    let categoryId = categorySelect.value;
    let dateOfExpense = document.querySelector("#expense_date").value;
    try{
        const res = await fetch(`../api/monthlyExpenses/${categoryId}&${dateOfExpense}`);
        const data = await res.json();
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}

//Other funcs
const setLimitValue = async () => {
    let category_limit = await getLimitForCategory();
    let limitMessageSpan = document.querySelector("#limit-alert-limit-value");
    limitMessageSpan.textContent = category_limit.category_limit;
}

setLimitValue();

const setStatusOfExpense = async () => {
    let status = "idle";
    let limit = document.querySelector("#limit-alert-limit-value").textContent;
    if (limit == '') limit = null;
    else limit = parseFloat(limit);
    let monthlyExpenses = 0.0;
    let jsonMonthlyExpenses = await getMonthlySumOfExpensesForCategory();
    if (jsonMonthlyExpenses.categorySum != null) monthlyExpenses = parseFloat(jsonMonthlyExpenses.categorySum);
    let currentExpenseValue = parseFloat(expenseValueInput.value);
    let sumExpenses = monthlyExpenses + currentExpenseValue;
    if ((limit != null && sumExpenses > 0.0) || sumExpenses < limit) status = "OK";
    else if (limit != null && sumExpenses >= limit) status = "WARN";

    return status;
}

const showExpenseStatus = async () => {
    let expenseStatus = await setStatusOfExpense();
    let limitMessageText = document.querySelector("#limit-alert-message");
    let limitMessageField = document.querySelector("#limit-alert");
    switch (expenseStatus){
        case 'OK':
            limitMessageText.textContent = "Jest OK!";
            limitMessageField.removeAttribute("hidden");
            limitMessageField.classList.remove("alert-warning");
            limitMessageField.classList.add("alert-info");
        break;
        case 'WARN':
            limitMessageText.textContent = "No i klops";
            limitMessageField.removeAttribute("hidden");
            limitMessageField.classList.remove("alert-info");
            limitMessageField.classList.add("alert-warning");
        break;
        case 'idle':
        default:
            limitMessageText.textContent = "";
            limitMessageField.setAttribute("hidden","");
    }
}