<?php

    include_once( './include/class.excelmgr.php' );

    $response = array(
        'status' => 'fail',
        'data' => [],
    );

    if ( isset( $_POST['action'] )) {
        if ( $_POST['action'] == 'import' ) {
            if ( isset( $_FILES['importFile'] ) ) {
                $excel_mgr = new ExcelManagement();
                $response = $excel_mgr->importExcelData( $_FILES['importFile'] );
            }
        } else if ( $_POST['action'] == 'view' ) {
            if ( isset( $_POST['category_id'] ) ) {
                $excel_mgr = new ExcelManagement();
                $response['status'] = 'success';
                //$response['data'] = array('subjects' => $excel_mgr->getSubjectNames( $_POST['category_id'] ), 'data' => $excel_mgr->getRankingDataByCategory( $_POST['category_id'] ) );
                $response['data'] = $excel_mgr->getTableContent( $_POST['category_id'] );
            }
        }
    } else {
        $response['data'] = "You must specify the correct parameter.";
    }

    echo json_encode($response);
    