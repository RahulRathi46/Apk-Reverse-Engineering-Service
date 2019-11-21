var count = 3;

setInterval(post_run,30000);

function run(loc){

    setTimeout(function(){
        $("#progress").css("width","95%");
        $('#msg').html("Task Completed Redirecting to results .....");
    }, 3);

    setTimeout(function(){
        window.location = loc.action;
    }, 1);
}

function post_run() {
    $("#warning-dev").css("display", "none");
    $.ajax({
        progress: function () {
            count = count + 10;
            $("#progress").css("width", String.valueOf(count).concat('%'));
        },
        url: '/update/' + uid, // point to server-side PHP script
        dataType: 'text', // what to expect back from the PHP script
        cache: false,
        contentType: false,
        processData: false,
        type: 'post',
        success: function (response) {
            response = $.parseJSON(response);
            if (response.status === '200') {
                $("#progress").css("width", "85%");
                $('#msg').html("Runnig Malware Test .....");
                run(response);
                clearInterval();
            } else if (response.status === '404') {
                $("#progress-dev").css("display", "none");
                $("#error-dev").css("display", "block");
                $('#emsg').html(response.msg);
            } else if (response.status === '500') {
                $("#progress").css("width", "85%");
                $('#msg').html(response.msg);
            }
        },
        error: function (response) {
            if (count) {
                count = count - 1;
            } else {
                try {
                    response = $.parseJSON(response);
                    if (response.msg) {
                        $('#emsg').html(response.msg);
                    } else {
                        $('#emsg').html("INTERNAL SERVER ERROR HTTP 1.0 500");
                    }

                } catch (e) {
                    $('#emsg').html(response.statusText + " status : " + response.status);
                }

                $("#warning-dev").css("display", "none");
                $("#progress-dev").css("display", "none");
                $("#error-dev").css("display", "block");
            }
        }
    });
}