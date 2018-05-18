function uniqueIdCheckForInput() {
    var value = document.getElementById('unique_id_initial_base').value;
    if (isNaN(value)) {
        document.getElementById('unique_id_message').innerText = 'مقدار عددی وارد کنید';
        document.getElementById('unique_id_initial_base').value = '';
        document.getElementById('submit').disabled = true;
        return;
    }
    if (parseInt(value) > 9999) {
        document.getElementById('unique_id_message').innerText = 'ورودی باید حداکثر چهار رقمی باشد';
        document.getElementById('unique_id_initial_base').value = '';
        document.getElementById('submit').disabled = true;
        return;
    }
    if (value == '') {
        document.getElementById('submit').disabled = true;
        return;
    }
    document.getElementById('submit').disabled = false;
    document.getElementById('unique_id_message').innerText = '';

}

function unique_id_get_new_id() {
    var link = ajaxurl;
    jQuery('#unique_id_new_request').disabled = true;
    jQuery('#unique_id_new_result').val('');
    jQuery.ajax({
        type: 'POST',
        url: link,
        data: {
            action: 'unique_id_generate_new_id'
        },
        success: function (result) {
            var data = JSON.parse(result);
            jQuery('#unique_id_new_result').val(data.id);
            jQuery('#unique_id_new_request').disabled = false;
        },
        error: function () {}
    });
}