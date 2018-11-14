<?php
    $import_csss = [];
    include_once( './views/layout/header.php' );
    include_once( './views/layout/menu.php' );
?>

<?php
    include_once( './views/template/import.php' );
?>

<?php
    $import_jss = array(
        array( 'name' => 'setting', 'dir'=> '' ),
    );
    include_once( './views/layout/footer.php' );
?>