jQuery.validator.addMethod("greaterThan",
function (value, element, params) {
    if (!/Invalid|NaN/.test(new Date(value))) {
        return new Date(value) > new Date($(params).val());
    }

    return isNaN(value) && isNaN($(params).val())
        || (Number(value) > Number($(params).val()));
});
$(document).ready(validation());
$("#endBalancePeriod").focusout(validation())
$("#startBalancePeriod").focusout(validation())
$("button[type=submit]").hover(validation())
function validation() {
$('#anyPeriodForm').validate({
    rules: {
        endBalancePeriod: {
            greaterThan: "#startBalancePeriod",
            validDate: true
        }
    },
    messages: {
        endBalancePeriod: {greaterThan: "Data końcowa musi być późniejsza niż początkowa!"}
    }
});
}
