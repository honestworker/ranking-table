<?php

define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', '' );
define( 'DB_NAME', 'university_ranking' );

include_once( './include/classes/PHPExcel.php' );
include_once( './include/classes/class.db.php' );

ini_set('max_execution_time', 300);

class ExcelManagement {

    private $db;

    public function __construct() {
        $this->db = new DB();
    }

    public function db_connect() {
        $connect = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME );
        if ( $connect ) {
            $this->connect = $connect;
            return '';
        } else {
            return "Database Connection failed: " . mysqli_connect_error();
        }
    }

    public function db_close() {
        if ( $this->connect ) {
            mysqli_close( $this->connect );
            $this->connect = null;
        }
    }

    private function checkCategory( $name ) {
        $valid_name = str_replace( "'" , "\'", $name );
        if ($row = $this->db->get_row( "SELECT id FROM category WHERE name = '{$valid_name}'" ) ) {
            return (int)$row[0];
        } else {
            $this->db->insert( 'category' , array( 'name' => $valid_name, ));
            return $this->db->lastid();
        }
    }

    private function checkSubject( $category_id, $name ) {
        $valid_name = str_replace( "'" , "\'", $name );
        if ( strtolower($valid_name) != 'ranking' ) {
            if ($row = $this->db->get_row( "SELECT id FROM subject WHERE name = '{$valid_name}' AND category_id = '{$category_id}'" ) ) {
                return (int)$row[0];
            } else {
                $this->db->insert( 'subject' , array( 'name' => $valid_name, 'category_id' => $category_id, ));
                return $this->db->lastid();
            }
        }
    }

    private function checkData( $category_id, $subject_ids, $data ) {
        $insert_count = 0;
        if ( $data[0] ) {
            $rank_value = $data[0];
            for ( $column = 1; $column < count($data); $column++ ) {
                if ( $column <= count($subject_ids) ) {
                    $valid_value = str_replace( "'" , "\'", $data[$column] );
                    if ( strtolower($valid_value) == 'ranking' ) {
                        $rank_value = $valid_value;
                    } else {
                        $row = $this->db->get_row( "SELECT id FROM ranking_data WHERE value = '{$valid_value}' AND rank_value = '{$rank_value}' AND category_id = '{$category_id}' AND subject_id = '{$subject_ids[$column - 1]}'" );
                        if ( !$row ) {
                            $this->db->insert( 'ranking_data' , array( 'rank_value' => $rank_value, 'value' => $valid_value, 'category_id' => $category_id, 'subject_id' => $subject_ids[$column - 1], ));
                            $insert_count = $insert_count + 1;
                        }                        
                        $rank_value = $data[0];
                    }
                }
            }
        }
        return $insert_count;
    }

    public function importExcelData( $file ) {
        $response = array(
            'status' => 'fail',
            'message' => '',
            'data' => []
        );

        $response['message'] =  $this->db_connect();
        if ( $response['message'] ) {
            return $response;
        }

        try {
            $fileName = $file['tmp_name'];
            $fileType = PHPExcel_IOFactory::identify( $fileName );
            $objReader = PHPExcel_IOFactory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );
        } catch ( Exception $e) {
            $response['message'] = "Error loading file: '" . $fileName . "' " . $e->getMessage();
            return $response;
        }

        $insert_count = 0;
        foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
            $category_id = $this->checkCategory( $worksheet->getTitle() );

            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $subject_ids = [];

            for ( $row = 1; $row < $highestRow; $row++ ) {
                $rowData = $worksheet->rangeToArray( 'A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE );
                if ( $row == 1 ) {
                    for ( $column = 1; $column < count( $rowData[0] ); $column++ ) {
                        if ( $rowData[0][$column] ) {
                            $subject_ids[] = $this->checkSubject( $category_id, $rowData[0][$column] );
                        }
                    }
                } else {
                    $insert_count = $insert_count + $this->checkData( $category_id, $subject_ids, $rowData[0] );
                }
            }
        }

        $response['status'] = "success";
        $response['message'] = "The data has been imported succssfully.(" . $insert_count . ")";
        return $response;
    }
}