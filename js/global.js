$(document).ready(function() {
    $(".loadTable").each(function() {
        loadTable(this)
    })
})

function loadTable(selector) {
    $(selector).after(
    `<div class="row tableLoadingSpinner">
        <div class="col-1 mx-auto">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>`)
    $.ajax({
        url: $(selector).data("generator"),
        success: function(response) {
            $(selector).html(response)
            $(selector).parent().find(".tableLoadingSpinner").remove()
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $(selector).parent().find(".tableLoadingSpinner").remove()
        }
    })
}