
/**
 * Add jQuery Validation plugin method for a valid password
 *
 * Valid passwords contain at least one letter and one number
 */
 $.validator.addMethod('validPassword',
 function(value, element, param) {
	 if (value != '') {
		 if (value.match(/.*[a-z]+.*/i) == null) {
			 return false;
		 }
		 if (value.match(/.*\d+.*/) == null) {
			 return false;
		 }
	 }
	 
	 return true;
 },
 'Must contain at least one letter and one number'
);
/**
* Add jQuery Validation plugin method for a valid password
*
* Valid passwords contain at least one letter and one number
*/
$.validator.addMethod('validAmount',
function(value, element, param) {
	if (value != '') {
		if (value.match(/(?!^0*$)(?!^0*\.0*$)^\d*((\.\d{1,2})|(,\d{1,2}))?$/) == null) {
			return false;
		}
	}
	
	return true;
},
'Proszę podać kwotę w odpowiednim formacie z dokładnością do 2 miejsc po przecinku'
);
/**
* Add jQuery Validation plugin method for a valid password
* 
* Valid passwords contain at least one letter and one number
*/
$.validator.addMethod('validDate',
function(value, element, param) {
	if (value != '') {
		if (value.match(/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/) == null) {
			return false;
		}
	}
	
	return true;
},
'Data powinna być w formacie YYYY-mm-dd'
);

let dateInput = document.querySelector("#expense_date");
if (dateInput !== null){
	if (dateInput.value === "" ) {
		let date = new Date();
		let day = date.getDate();
		if (day < 10){
			day = `0${day}`;
		}
		let month = date.getMonth() + 1;
		let year = date.getFullYear();
		let currentDate = `${year}-${month}-${day}`;
		dateInput.value = currentDate;
	}
}

function addCategory(btn, setting){
	var form = $(btn).parent().parent();//form
	var input = form.find('.input-group').last();
	var newInputId = getRandomInt(0,999999999);
	var newInputGroup = input.clone(true);
	newInputGroup.show();
	var newInput = newInputGroup.find('input').first();
	var newInputName = setting + '_' + newInputId +'_new';
	newInput.attr('name', newInputName);
	newInput.val('');
	switch (setting) {
		case 'expense':
			newInput.attr('placeholder', 'Podaj nazwę kategorii');
			$('#expenseCategories').append(newInputGroup);
			break;
		case 'income':
			newInput.attr('placeholder', 'Podaj nazwę kategorii');
			$('#incomeCategories').append(newInputGroup);
			break;
		case 'payment':
			newInput.attr('placeholder', 'Podaj sposób płatności');
			$('#paymetMethods').append(newInputGroup);
			break;
	}
}

function getRandomInt(min, max) {
	min = Math.ceil(min);
	max = Math.floor(max);
	return Math.floor(Math.random() * (max - min) + min);
}

function validateSetting(setting){
	var status = false;
	$.ajax({
		url: "/settings/validateRemoval?setting="+setting,
		async: false
	})
		.done(function(data){
			if (data != 'false'){
				status = true;
			}
		});
	return status;
}

function closeDeleteModal(){
	$('#deleteModal').modal('hide')
}

function closeUpdateModal(){
	$('#updateModal').modal('hide')
}

$(document).ready(function() {
	$('#formUpdatePassword').validate({
		rules: {
			password: {
				required: true,
				minlength: 6,
				validPassword: true
			}
		}
	});

	/**
	 * Show password toggle button
	 */
	$('#inputPassword').hideShowPassword({
		show: false,
		innerToggle: 'focus'
	});
});

function confirmCashflowRemoval(cashflowType, cashflowId){
	$('#confirmCashflowRemoveModal').on('show.bs.modal', function () {
		var modal = $(this)
		modal.find('#deleteButton').attr('href', '/'+cashflowType+'/remove?id='+cashflowId)
	  });
	$('#confirmCashflowRemoveModal').modal('show');
}

function closeCashflowRemoveModal(){
	$('#confirmCashflowRemoveModal').modal('hide')
}

let removeButtons = document.querySelectorAll(".remove-btn");

for (let i = 0; i<removeButtons.length; i++) {
    let button = removeButtons[i];
    button.addEventListener("click", async () => {
		let butttonInput = button.parentElement.querySelector("input");
		let settingName = butttonInput.value;
		let settingType = butttonInput.name.split("_")[0];//type_id
		let settingId = butttonInput.name.split("_")[1];//type_id
		removeResponse = await removeCategory(settingType, settingId)
		if (await removeResponse.status != "false"){
			showDeleteModal(settingName,settingType);
		} else {
			window.location = `../settings/remove/${settingType}/${settingName}`;
		}

    })
}

let settingNameInputs = document.querySelectorAll("input.settings-table");
for (let i = 0; i<settingNameInputs.length; i++) {
    let input = settingNameInputs[i];
    input.addEventListener("keyup", async (e) => {
		await verifyUpdate(e.target);
	});
    input.addEventListener("change", async (e) => {
		await verifyUpdate(e.target);
	});
    input.addEventListener("focusin", async (e) => {
		if ((!( await isSingleSettingChanged()) && !(await isFocusOnModifiedElement(e.target)))){
			showUpdateModal(e.target);
			input.removeAttribute("readonly");
		}

	});
    input.addEventListener("focusout", async (e) => {
		input.setAttribute("readonly", "readonly");
		// if ((isSingleSettingChanged() && !isFocusOnModifiedElement(e.target))
		// || (!isSingleSettingChanged() && !isFocusOnModifiedElement(e.target))){
		// 	showUpdateModal(e.target);
		//}
	});
    input.addEventListener("click", async () => {
		input.removeAttribute("readonly");
	});
}

const removeCategory = async (type, settingId) => {
    try{
        const res = await fetch(`../api/settings/remove/${type}?settingId=${settingId}`);
        const data = await res.json();
        return data;
    }catch (e){
        console.log("ERROR", e);
    }
}

const showDeleteModal = (settingName,settingType) =>{
	$('#deleteModal').on('show.bs.modal', function () {
		var modal = $(this)
		modal.find('.modal-content').attr('name', settingType)
		modal.find('.category-name').text(settingName);
		modal.find('.confirm-btn').attr('href', `/settings/remove/${settingType}/${settingName}`)
	  });
	$('#deleteModal').modal('show');

}

const showUpdateModal = (input) => {
	let modifiedInputDiv = document.querySelector(".update-btn.modified").parentElement;
	let modifiedInput = modifiedInputDiv.querySelector("input");
	let settingType = modifiedInput.name.split("_")[0];//type_id
	let settingId = modifiedInput.name.split("_")[1];//type_id
	let settingName = modifiedInput.value;

	$('#updateModal').on('show.bs.modal', function () {
		let modal = document.querySelector("#updateModal");
		modal.querySelector('.confirm-btn').setAttribute('href', `/settings/update/${settingType}/${settingId}/${settingName}`)
		});
	$('#updateModal').modal('show');
}

const isSingleSettingChanged = () =>{
	let settingsToUpdate = document.querySelectorAll(".update-btn.modified");
	if (settingsToUpdate.length >= 1){
		return false;
	}

	return true;
}

const verifyUpdate = async(input) =>{
	let settingType = input.name.split("_")[0];//type_id
	let settingId = input.name.split("_")[1];//type_id
	let settingName = input.value;
	let status = await validateName(settingType, settingName, settingId)
	let button = input.parentElement.querySelector(".update-btn");
	if (status.name_status == "false"){
		input.parentElement.querySelector("label.error").classList.add("d-none");
		if (isSingleSettingChanged()) {
			button.classList.remove("d-none");
			button.classList.add("modified");
		}
		button.setAttribute("onclick", `window.location.href='../settings/update/${settingType}/${settingId}/${settingName}'`);
	} else if(status.name_status == "void"){
		input.parentElement.querySelector(".update-btn").classList.add("d-none");
		input.parentElement.querySelector("label.error").classList.add("d-none");
		button.classList.remove("modified");
	} else {
		input.parentElement.querySelector(".update-btn").classList.add("d-none");
		input.parentElement.querySelector("label.error").classList.remove("d-none");
		input.parentElement.querySelector("label.error").innerText = status.name_status;
		button.classList.remove("modified");
	};
}

const isFocusOnModifiedElement = async (updateModalInputElement) => {
	updateBtn = updateModalInputElement.parentElement.querySelector(".update-btn");
	if (updateBtn.classList.contains("modified")){
		return true;
	}

	return false;
}