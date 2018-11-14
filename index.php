<?php
    $import_csss = [];
    include_once( './views/layout/header.php' );
    include_once( './views/layout/menu.php' );
?>

<?php
    if ( isset( $_GET['page'] )) {
        include_once( "./views/template/{$_GET['page']}.php" );
    } else {        
        include_once( './views/template/table.php' );
    }
?>

<?php
    $import_jss = [];
    include_once( './views/layout/footer.php' );
?>