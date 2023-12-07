//Event Listeners
    //Dropdown
let categorySelect = document.querySelector("#expense_category");
categorySelect.addEventListener("change", async () => {
    await setLimitValue();
    await showExpenseStatus();
})
    //Value input
let expenseValueInput = document.querySelector("#expense_amount");
expenseValueInput.addEventListener("change", async () => {
    await showExpenseStatus();
})
expenseValueInput.addEventListener("keydown", async () => {
    await showExpenseStatus();
})
    //Datepicker
let expenseDate = document.querySelector("#expense_date");
expenseDate.addEventListener("change", async () => {
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

const getMonthlySumOfExpensesForCategory = async (id="0") => {
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
    let limitMessageSpan = document.querySelector("#limit-value");
    limitMessageSpan.value = category_limit.category_limit;
}

setLimitValue();

const setStatusOfExpense = async () => {
    let status = "idle";
    let limit = document.querySelector("#limit-value").value;
    if (limit == '') limit = null;
    else limit = parseFloat(limit);
    let sumExpenses = await setSumOfExpenses();
    if ((limit != null && sumExpenses < limit) || limit != null && !sumExpenses) status = "OK";
    else if (limit != null && sumExpenses >= limit) status = "WARN";
    return status;
}

const showExpenseStatus = async () => {
    let expenseStatus = await setStatusOfExpense();
    let limitMessageText = document.querySelector("#limit-alert-message");
    let limitMessageField = document.querySelector("#limit-alert");
    let limitValue = document.querySelector("#limit-value").value;
    let expenses = document.querySelector("#sum-of-expenses").value;
    let difference = parseFloat(limitValue - expenses).toFixed(2);
    switch (expenseStatus){
        case 'OK':
            limitMessageText.textContent = `Jest OK! W tym miesiącu kwota wydatków: ${expenses} jest poniżej kwoty limitu: ${limitValue}PLN. Do wykorzystania w ramch limitu pozostało: ${difference}PLN`;
            limitMessageField.removeAttribute("hidden");
            limitMessageField.classList.remove("alert-warning");
            limitMessageField.classList.add("alert-info");
        break;
        case 'WARN':
            limitMessageText.textContent = `Uwaga! W tym miesiącu kwota wydatków: ${expenses}PLN przekroczyła kwotę limitu: ${limitValue}PLN o: ${-1*difference}PLN`;
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

const setSumOfExpenses = async () => {
    let monthlyExpenses = 0.0;
    let jsonMonthlyExpenses = await getMonthlySumOfExpensesForCategory();
    if (jsonMonthlyExpenses.categorySum != null) monthlyExpenses = parseFloat(jsonMonthlyExpenses.categorySum);
    let currentExpenseValue = parseFloat(expenseValueInput.value);
    let sumExpenses = monthlyExpenses + currentExpenseValue;
    if (window.location.href.includes("edit")){
        savedCurrentExpenseValue = document.querySelector("#curentExpenseValue").value;
        sumExpenses -=savedCurrentExpenseValue;
    }
    let sumValue = document.querySelector("#sum-of-expenses");
    if (sumExpenses ) sumValue.value = sumExpenses;
    return sumExpenses;
}