$( document ).ready(function () {
    $("#importBtn").click(function (event) {
        event.preventDefault();
        var form = $("#importForm");
        var params = form.serializeArray();        
        var files = $("#importFile")[0].files;
        var formData = new FormData();

        for (var i = 0; i < files.length; i++) {
            formData.append('importFile', files[i]);
        }

        $(params).each(function (index, element) {
            formData.append(element.name, element.value);
        });

        formData.append('action', 'import');

        var importBtn = $(this);
        importBtn.text("Uploading...");
        importBtn.prop("disabled", true);

        $.ajax({
            url: "api.php", //You can replace this with MVC/WebAPI/PHP/Java etc
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            success: function ( data ) {
                var response = JSON.parse( data );
                alert( response.message );
                if ( response.status == 'success' ) {                    
                    importBtn.prop("disabled", false);
                    importBtn.text("Import");
                    $("#importFile").val("");
                }
            },
            error: function (error) { alert(error); }

        });
    });
});