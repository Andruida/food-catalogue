function submit() {
    $("#loadingSpinner").show()

    $(".userInput").removeClass("is-invalid").removeClass("is-valid")
    
    var allValid = true;

    $(".userInput.required").each(function() {
        if ($(this).val().length == 0) {
            allValid = false;
        }
    })

    if (allValid == false) {
        $("#loadingSpinner").hide()
        $(".userInput").addClass("is-invalid")
        return;
    }

    $.ajax({
        url: "/api/login.php",
        method: "POST",
        data: {
            username: $("#username").val(),
            password: $("#password").val()
        },
        success: function(response) {
            $("#loadingSpinner").hide()
            if (response == "INVALID") {
                $(".userInput").addClass("is-invalid")
                return
            }
            window.location = "/";
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#loadingSpinner").hide()
        }
    })
}