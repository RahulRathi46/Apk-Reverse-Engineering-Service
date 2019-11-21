function run(loc){

    setTimeout(function(){
        $("#progress").css("width","95%");
        $('#msg').html("Adding to queue .....");
    }, 1);

    setTimeout(function(){
        window.location = loc.action;
    }, 2);
}

$('#browse').on('click', function () {
    $('#file').click();
});

$(document).ready(function (e) {
    $('#file').change(function () {
        $('#msg').html("Uploading .....");
        $("#upload-dev").css("display", "none");
        $("#warning-dev").css("display", "none");
        $("#progress-dev").css("display", "block");
        var file_data = $('#file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        $.ajax({
            progress : function(){
                count = count + 10;
                $("#progress").css("width", String.valueOf(count).concat('%'));
            },
            url: '/upload', // point to server-side PHP script
            dataType: 'text', // what to expect back from the PHP script
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                response = $.parseJSON(response);
                if(parseInt(response.status) === 200) {
                    $("#progress").css("width","85%");
                    $('#msg').html("Runnig Malware Test .....");
                    run(response);
                }else if (parseInt(response.status) === 404) {
                    $("#upload-dev").css("display", "none");
                    $("#progress-dev").css("display", "none");
                    $("#error-dev").css("display", "block");
                    $('#emsg').html(response.msg);
                }
            },
            error: function (response) {
                try {
                    response = $.parseJSON(response);
                    if (response.msg) {
                        $('#emsg').html(response.msg);
                    }else {
                        $('#emsg').html("INTERNAL SERVER ERROR HTTP 1.0 500");
                    }

                }catch (e) {
                    $('#emsg').html(response.statusText);
                }

                $("#upload-dev").css("display", "none");
                $("#progress-dev").css("display", "none");
                $("#error-dev").css("display", "block");
            }
        });
    });
});
