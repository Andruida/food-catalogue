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
            mealType: $("#foodMealType").val(),
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
                url: "/api/log-food-options.php?field="+encodeURIComponent(k),
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
var bsModal;

function openRating(id, type, rating, name) {
    var modal = $("#rateModal");

    modal.data("course-id", id)
    modal.data("course-type", type)
    modal.data("active", true)

    modal.find("#ratedFoodName").text(name);
    if (rating === false) {
        modal.find("#foodRating").val(55);
        $("#zeroRating").prop("checked", false)
        ratingChange(true);
    } else {
        modal.find("#foodRating").val(rating*100);
        $("#zeroRating").prop("checked", rating == 0)
        ratingChange()
    }

    bsModal.show();
}

function closeRating() {
    var modal = $("#rateModal");

    modal.data("active", false)
    modal.data("course-id", undefined)
    modal.data("course-type", undefined)
    bsModal.hide()
}

function saveRating() {
    var modal = $("#rateModal");
    if (!modal.data("active")) return;

    $("#generalModalError").hide()
    $("#modalLoadingSpinner").show()
    $("#modalSubmitBtn").prop("disabled", true)

    var rating = ratingChange();
    $.ajax({
        url: "/api/rate.php",
        method: "POST",
        data: {
            rating,
            id: modal.data("course-id"),
            type: modal.data("course-type")
        },
        success: function(response) {
            $("#modalLoadingSpinner").hide()
            $("#modalSubmitBtn").prop("disabled", false)
            closeRating()
            loadTable("#logTable")
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#modalLoadingSpinner").hide()
            $("#modalSubmitBtn").prop("disabled", false)
            if (jqXHR.status == 401) {
                window.location = "/login";
                return;
            }
            $("#generalModalError").show()
        }
    })

    
}

function ratingChange(reset) {
    if (reset){
        $("#rateModal .modal-body h2 span").text("-.-");
        return Number.NaN;
    } else {
        var rating;
        if ($("#zeroRating").prop("checked"))
            rating = 0;
        else
            rating = ($("#foodRating").val() / 10).toFixed(1)
        $("#rateModal .modal-body h2 span").text(rating);
        return rating/10;
    }
}

$(document).ready(function() {
    $(".dishInput").each(function() {
            $(this).bind("input", checkFoodExists.bind(this, this))
        })

    bsModal = new bootstrap.Modal("#rateModal")
})
