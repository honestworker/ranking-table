<?php

    include_once( './include/class.excelmgr.php' );

    $response = array(
        'status' => 'fail',
        'message' => '',
        'data' => []
    );

    $excel_mgr = new ExcelManagement();

    if ( isset( $_POST['action'] )) {
        if ( $_POST['action'] == 'import' ) {
            if ( isset( $_FILES['importFile'] ) ) {
                $response = $excel_mgr->importExcelData( $_FILES['importFile'] );
            }
        }
    } else {
        $response['message'] = "You must specify the correct parameter.";
        echo json_encode($response);
    }

    echo json_encode($response);
    