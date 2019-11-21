if (show_state == 0) {
    show_error();

} else {
    download_test();
}

function show_error(){
    $("#download-dev").css("display", "none");
    $("#progress-dev").css("display", "none");
    $("#download-dev-2").css("display", "none");
    $("#error-dev").css("display", "block");

    setTimeout(function () {
        window.location = "/";
    }, 1);
}

function download_test(){
    setTimeout(function () {
        $("#progress").css("width", "85%");
        $('#msg').html("Inspecting Zip Achive .....");
    }, 2);


    setTimeout(function () {
        $("#progress").css("width", "95%");
        $('#msg').html("Trigerring Download .....");
    }, 3);


    setTimeout(function () {
        $("#progress-dev").css("display", "none");
        $("#download-dev").css("display", "block");
    }, 4);
}