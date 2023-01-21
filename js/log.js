function submit() {
    $("#loadingSpinner").show()
    $("#nameEmptyError").hide()
    $("#nameNotFoundError").hide()
    $("#successAlert").hide()
    $("#failAlert").hide()
    $("#submitBtn").prop("disabled", true)

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
        $("#submitBtn").prop("disabled", false)
        return;
    }

    $.ajax({
        url: "/api/log.php",
        method: "POST",
        data: {
            name: $("#foodName").val(),
            date: $("#foodDate").val(),
            createIfNeeded: $("#foodCreateIfNeeded").prop('checked')
        },
        success: function(response) {
            $("#loadingSpinner").hide()
            $("#submitBtn").prop("disabled", false)
            if (response == "NOTFOUND") {
                $("#foodName").addClass("is-invalid")
                $("#nameEmptyError").hide()
                $("#nameNotFoundError").show()
                return
            }
            loadTable("#logTable")
            loadStoredFoods()
            $("#successAlert").show()
            $("#foodName").val("")
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


function loadStoredFoods() {
    $.ajax({
        url: "/api/log-food-options.php",
        success: function(response) {
            $("#storedFoods").html(response)
        }
    })
    
}

$(document).ready(loadStoredFoods)
