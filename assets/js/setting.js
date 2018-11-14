$( document ).ready(function () {
    $("#importBtn").click(function (event) {
        event.preventDefault();
        var form = $("#importForm");
        var params = form.serializeArray();        
        var files = $("#importFile")[0].files;
        if ( files.length ) {
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                if ( files[i].name ) {
                    var extension = files[i].name.replace(/^.*?\.([a-zA-Z0-9]+)$/, "$1");
                    if ( extension != 'xls' && extension != 'xlsx' ) {
                        alert("You must specify the excel file.");
                        return;
                    }
                }
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
                    alert( response.data );
                    if ( response.status == 'success' ) {
                        importBtn.prop("disabled", false);
                        importBtn.text("Import");
                        $("#importFile").val("");
                    }
                },
                error: function (error) { alert(error); }    
            });
        } else {
            alert("You must specify the importable file.");
        }
    });
});