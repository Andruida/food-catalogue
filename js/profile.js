function submit() {
    $("#loadingSpinner").show()
    $("#successAlert").hide()
    $("#failAlert").hide()
    $("#submitBtn").prop("disabled", true)

    $(".userInput").removeClass("is-invalid").removeClass("is-valid")

    if ($("#newPassword").val().length < 8) {
        $("#newPassword").addClass("is-invalid")
        $("#loadingSpinner").hide()
        $("#submitBtn").prop("disabled", false)
        return;
    }

    if ($("#newPasswordAgain").val() != $("#newPassword").val()) {
        $("#newPasswordAgain").addClass("is-invalid")
        $("#loadingSpinner").hide()
        $("#submitBtn").prop("disabled", false)
        return;
    }

    $.ajax({
        url: "/api/change-password.php",
        method: "POST",
        data: {
            password: $("#newPassword").val()
        },
        success: function(response) {
            $("#successAlert").show()
            $("#loadingSpinner").hide()
            $("#submitBtn").prop("disabled", false)
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#loadingSpinner").hide()
            $("#submitBtn").prop("disabled", false)
            if (jqXHR.status == 401) {
                window.location = "/login";
                return;
            }
            $("#failAlert").show()
        }
    })
}