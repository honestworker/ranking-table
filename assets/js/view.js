$(document).ready(function() {
    var ranking_table = $('#ranking_table').DataTable( {
        'pageLength':   100,
        'destroy'       : true,
        'paging'        : true,
        'ordering'      : true,
        'order'         : [[0, "asc"]]
    });
});