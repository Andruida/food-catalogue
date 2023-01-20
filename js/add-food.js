function submit() {
    $("#loadingSpinner").show()
    $("#nameEmptyError").hide()
    $("#nameDuplicateError").hide()

    $(".foodInput").removeClass("is-invalid").removeClass("is-valid")
    
    var allValid = true;

    $(".foodInput.required").each(function() {
        if ($(this).val().length == 0) {
            $(this).addClass("is-invalid")
            allValid = false;
            if ($(this).prop("id") == "foodName") {
                $("#nameEmptyError").show()
            }
        }
    })

    if (allValid == false) {
        $("#loadingSpinner").hide()
        return;
    }

    $.ajax({
        url: "/api/add-food.php",
        method: "POST",
        data: {
            name: $("#foodName").val(),
            description: $("#foodDesc").val(),
            addToLog: $("#foodAddToLog").prop('checked')
        },
        success: function(response) {
            $("#loadingSpinner").hide()
            if (response == "DUPLICATE") {
                $("#foodName").addClass("is-invalid")
                $("#nameEmptyError").hide()
                $("#nameDuplicateError").show()
                return
            }
            $(".foodInput").empty()
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#loadingSpinner").hide()
        }
    })
}