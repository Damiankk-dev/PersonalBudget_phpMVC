let limitButtons = document.querySelectorAll(".expense_limit");
for (let i = 0; i < limitButtons.length; i++){
    limitButtons[i].addEventListener("click", () => {
        showLimitModal();
    })
}

let limitModalCheckbox = document.querySelector("#add-limit");
limitModalCheckbox.addEventListener("click", () => {
    if (limitModalCheckbox.checked === true){
        document.querySelector(".limit-modal-input-number").removeAttribute("disabled");
    } else {
        document.querySelector(".limit-modal-input-number").setAttribute("disabled", "");
    };
})
const showLimitModal = () => {
    $('#limitModal').on('show.bs.modal', function (e) {
        var modal = $(this);
        modal.find(".limit-category-name").text("XD")
      });
    $('#limitModal').modal('show');
}

const closeLimitModal = () => $('#limitModal').modal('hide');