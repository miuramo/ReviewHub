function ft_changed(formName, file_id, filetype_id) {
    // alert(formName + " " + file_id + " " + filetype_id);
    var form = $("#" + formName);
    $('#' + formName + '_file_id').val(file_id);
    $('#' + formName + '_filetype_id').val(filetype_id);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: form.serialize(),
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            var ary = JSON.parse(result);
            var elem = $("#file_" + ary['file_id']);    
            elem.replaceWith('<span id="file_'+ary['file_id']+'" class="mx-1 mb-1 sm:rounded-lg border-2 border-green-600 bg-lime-200 px-2 py-1 font-bold text-green-600 text-lg dark:bg-lime-400">' + ary['ftname'] + '</span>');

            // elem.addClass('flash');
            // setTimeout(function () {
            //     elem.removeClass('flash');
            // }, 1000); // フラッシュの時間
            location.reload();
        },
        error: function (xhr, textStatus, error) {
            alert("error form submit (form changed, but not saved.)");
            console.log(textStatus);
        }
    });
}