function submit() {
    $("#failAlert").hide()
    $("#loadingSpinner").show()
    $("#submitBtn").attr("disabled", true)

    var userList = $("input[name='userSelect']")
        .toArray()
        .filter(function(e) {return $(e).prop("checked")})
        .map(function(e) {return $(e).val()})
        .join(',')

    var mealType = $("#mealSelect").val()

    $.ajax({
        url: "/api/picker.php?users="+encodeURIComponent(userList)+"&mealType="+encodeURIComponent(mealType),
        method: "GET",
        success: function(response) { 
            $("#loadingSpinner").hide()
            $("#submitBtn").attr("disabled", false)
            if (response == "NOTFOUND") {
                $("#failAlert").show()
            }
            $("#mealTable").html(response)
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#submitBtn").attr("disabled", false)
            $("#loadingSpinner").hide()
        }
    })
}