<?php
    $import_csss = [];
    $import_jss = [];
?>

<?php
    if ( isset( $_GET['page'] )) {
        include_once( "./views/template/{$_GET['page']}.php" );
    } else {        
        $import_csss = array(
            array( 'name' => 'jquery.dataTables.min', 'dir'=> '' ),
            array( 'name' => 'custom', 'dir'=> '' ),
        );
        include_once( './views/layout/header.php' );
        include_once( './views/layout/menu.php' );

        include_once( './views/template/table.php' );
        
        $import_jss = array(
            array( 'name' => 'jquery.dataTables.min', 'dir'=> '' ),
            array( 'name' => 'view', 'dir'=> '' ),
        );
        include_once( './views/layout/footer.php' );
    }
?>