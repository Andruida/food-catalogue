function submit() {
    $("#loadingSpinner").show()
    $("#emptyAlert").hide()
    $("#successAlert").hide()
    $("#failAlert").hide()
    $("#notFoundAlert").hide()
    $("#submitBtn").prop("disabled", true)

    $(".foodInput").removeClass("is-invalid").removeClass("is-valid")
    
    var allValid = true;

    $(".foodInput.required").each(function() {
        if ($(this).val().length == 0) {
            $(this).addClass("is-invalid")
            allValid = false;
        }
    })
    const foodEntryMode = $("input[name='foodEntryMode'][type='radio']:checked").val();

    if (foodEntryMode != 'noLog') {
        if ($("#foodDate").val().length == 0) {
            $("#foodDate").addClass("is-invalid")
            allValid = false;
        }
    }

    $(".foodInput[type='text']").each(function() {
        if ($(this).val().length > 100) {
            $(this).addClass("is-invalid")
            allValid = false;
        }
    })

    const emptyCourses = 
    ["#soupName", "#mainCourseName", "#sideDishName", "#dessertName"].every(function(selector) {
        return $(selector).val().length == 0;
    });

    if (emptyCourses === true) {
        $("#emptyAlert").show()
        allValid = false;
    }

    if (allValid === false) {
        $("#loadingSpinner").hide()
        $("#submitBtn").prop("disabled", false)
        return;
    }

    $.ajax({
        url: "/api/log.php",
        method: "POST",
        data: {
            soup: $("#soupName").val(),
            mainCourse: $("#mainCourseName").val(),
            sideDish: $("#sideDishName").val(),
            dessert: $("#dessertName").val(),
            date: $("#foodDate").val(),
            foodEntryMode: foodEntryMode || "createIfNeeded"
        },
        success: function(response) {
            $("#loadingSpinner").hide()
            $("#submitBtn").prop("disabled", false)
            if (response == "NOTFOUND") {
                $("#NotFoundAlert").show()
                return
            }
            loadTable("#logTable")
            loadStoredFoods()
            $("#successAlert").show()
            $(".dishInput").val("")
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
    $(".dishInput")
        .toArray()
        .map(function(v){return $(v).attr("list")})
        .forEach(function(k) {
            $.ajax({
                url: "/api/log-food-options.php?field="+k,
                success: function(response) {
                    $("#"+k).html(response)
                }
            })
        })
    
}

$(document).ready(loadStoredFoods)

function checkFoodExists(selector) {
    $(selector).removeClass("is-valid")
    const matches = $("#"+ $(selector).attr("list") )
        .children()
        .toArray()
        .map(function(v) {return $(v).text()})
        .some(function(v){return v == $(selector).val()})


    if (matches) {
        $(selector).addClass("is-valid")
    }
}


$(document).ready(function() {
    $(".dishInput").each(function() {
            $(this).bind("input", checkFoodExists.bind(this, this))
        })
})
