function submit() {
    $("#loadingSpinner").show()
    $("#submitBtn").attr("disabled", true)

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
            $("#submitBtn").attr("disabled", false)
            $("#loadingSpinner").hide()
            if (response == "INVALID") {
                $(".userInput").addClass("is-invalid")
                return
            }
            window.location = "/";
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#submitBtn").attr("disabled", false)
            $("#loadingSpinner").hide()
            console.log("login failed for some reason");
        }
    })
}


$(document).ready(function() {
$('.userInput').keypress(function (e) {
    if (e.which == 13) {
      submit();
      return false;
    }
  });
})