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

    private function table_exists( $name ) {
        $result = $this->db->get_row( "SHOW TABLES LIKE '{$name}'" );
        if ( $result ) {
            return true;
        } else {
            return false;
        }
    }

    private function checkCategory( $name ) {
        $valid_name = str_replace( "'" , "\'", $name );
        if ( $this->table_exists('category') ) {
            if ($row = $this->db->get_row( "SELECT id FROM category WHERE name = '{$valid_name}'" ) ) {
                return (int)$row[0];
            } else {
                $this->db->insert( 'category' , array( 'name' => $valid_name, ));
                return $this->db->lastid();
            }
        }
        return 0;
    }

    private function checkSubject( $category_id, $name ) {
        $valid_name = str_replace( "'" , "\'", $name );
        if ( $this->table_exists('subject') ) {
            if ( strtolower($valid_name) != 'ranking' ) {
                if ($row = $this->db->get_row( "SELECT id FROM subject WHERE name = '{$valid_name}' AND category_id = '{$category_id}'" ) ) {
                    return (int)$row[0];
                } else {
                    $this->db->insert( 'subject' , array( 'name' => $valid_name, 'category_id' => $category_id, ));
                    return $this->db->lastid();
                }
            }
        }
    }

    private function checkData( $category_id, $subject_ids, $subject_names, $data ) {
        $insert_count = 0;
        if ( $this->table_exists('ranking_data') ) {
            if ( $data[0] ) {
                $rank_value = $data[0];
                for ( $column = 1; $column < count($data); $column++ ) {
                    if ( $column <= count($subject_ids) && $column <= count($subject_names) ) {
                        $valid_value = str_replace( "'" , "\'", $data[$column] );
                        if ( $subject_names[$column] && $valid_value ) {
                            if ( strtolower($subject_names[$column]) == 'ranking' ) {
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
            }
        }
        return $insert_count;
    }

    public function importExcelData( $file ) {
        $response = array(
            'status' => 'fail',
            'data' => '',
        );

        $response['data'] =  $this->db_connect();
        if ( $response['data'] ) {
            return $response;
        }

        try {
            $fileName = $file['tmp_name'];
            $fileType = PHPExcel_IOFactory::identify( $fileName );
            $objReader = PHPExcel_IOFactory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );
        } catch ( Exception $e) {
            $response['data'] = "Error loading file: '" . $fileName . "' " . $e->getMessage();
            return $response;
        }

        if ( !$this->table_exists('category') ) {
            $response['status'] = "fail";
            $response['data'] = "The category table does not exist!";
            return $response;
        }
        if ( !$this->table_exists('subject') ) {
            $response['status'] = "fail";
            $response['data'] = "The subject table does not exist!";
            return $response;
        }
        if ( !$this->table_exists('ranking_data') ) {
            $response['status'] = "fail";
            $response['data'] = "The ranking_data table does not exist!";
            return $response;
        }

        $insert_count = 0;
        foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
            $category_id = $this->checkCategory( $worksheet->getTitle() );

            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $subject_ids = [];
            $subject_names = [];
            for ( $row = 1; $row <= $highestRow; $row++ ) {
                $rowData = $worksheet->rangeToArray( 'A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE );
                if ( $row == 1 ) {
                    $subject_names = $rowData[0];
                    for ( $column = 1; $column < count( $rowData[0] ); $column++ ) {
                        if ( $rowData[0][$column] ) {
                            $subject_ids[] = $this->checkSubject( $category_id, $rowData[0][$column] );
                        }
                    }
                } else {
                    $insert_count = $insert_count + $this->checkData( $category_id, $subject_ids, $subject_names, $rowData[0] );
                }
            }
        }

        $response['status'] = "success";
        $response['data'] = "The data has been imported succssfully.(" . $insert_count . ")";
        return $response;
    }

    public function allCategory() {
        if ( $this->table_exists('category') ) {
            return $this->db->get_results( "SELECT id, name FROM category;" );
        }
        return [];
    }

    public function getSubjects( $category_id ) {
        if ( $this->table_exists('subject') ) {
            return $this->db->get_results( "SELECT id, name FROM subject WHERE category_id= '{$category_id}';" );
        }
        return [];
    }

    public function getSubjectNames( $category_id ) {
        $result = [];
        if ( $this->table_exists('subject') ) {
            $subject_names = $this->db->get_results( "SELECT id, name FROM subject WHERE category_id= '{$category_id}';" );
            if ( $subject_names ) {
                if ( count($subject_names) ) {
                    foreach ( $subject_names as $subject_name ) {
                        $result[] = $subject_name['name'];
                    }
                }
            }
        }
        return $result;
    }

    public function getRankingData( $category_id, $subject_id ) {        
        if ( $this->table_exists('ranking_data') ) {
            return $this->db->get_results( "SELECT id, rank_value, value FROM ranking_data WHERE category_id= '{$category_id}' AND subject_id= '{$subject_id}' ORDER BY rank_value ASC;" );
        }
        return [];
    }

    public function getRankingDataByCategory( $category_id ) {
        $subjects = $this->getSubjects( $category_id );
        $result = [];
        $ranking_datas = [];
        $ranking_indexs = [];
        $max_rows = $index = 0;
        $min_rank = $focus_index = 1;
        if ( $subjects ) {
            if ( count($subjects) ) {
                foreach ( $subjects as $subject ) {
                    $ranking_data = $this->getRankingData( $category_id,  $subject['id'] );
                    if ( $ranking_data ) {
                        if ( $max_rows < count($ranking_data) ) {
                            $max_rows = count($ranking_data);
                            $focus_index = $index;
                        }
                        $min_rank = ( $min_rank > $ranking_data[0]['rank_value'] ) ? $ranking_data[0]['rank_value'] : $min_rank;
                    }
                    $ranking_datas[] = $ranking_data;
                    $ranking_indexs[] = 0;
                    $index = $index + 1;
                }
            }
        }
        if ( $max_rows ) {
            $ranking_row = 0;
            while ($ranking_row < $max_rows) {
                $rank_row = array($min_rank);
                $valid_count = $focus_flag = 0;
                for ( $column_index = 0;  $column_index < count($ranking_datas); $column_index++ ) {
                    $cell_data = "";
                    if ( $ranking_indexs[$column_index] < count($ranking_datas[$column_index]) ) {
                        if ( $ranking_datas[$column_index][$ranking_indexs[$column_index]]['rank_value'] == $min_rank ) {
                            $valid_count = $valid_count + 1;
                            $cell_data = $ranking_datas[$column_index][$ranking_indexs[$column_index]]['value'];
                            $ranking_indexs[$column_index] = $ranking_indexs[$column_index] + 1;
                            if ( $focus_index == $column_index ) {
                                $focus_flag = 1;
                            }
                        }
                    }
                    $rank_row[] = $cell_data;
                }
                if ($valid_count) {
                    $result[] = $rank_row;
                    if ( $focus_flag ) {
                        $ranking_row = $ranking_row + 1;
                    }
                } else {
                    $min_rank = $min_rank + 1;
                }
            }
        }
        return $result;
    }

    public function getTableContent( $category_id ) {
        $subjects = $this->getSubjectNames( $category_id );
        $ranking_datas = $this->getRankingDataByCategory( $category_id );

        $result = "<thead>\n";
        $result .= "<tr>\n";
        $result .= "<th>Ranking</th>\n";
        if ( $subjects ) {
            if ( count( $subjects ) ) {
                foreach ( $subjects as $subject ) {
                    $result .= "<th>" . $subject . "</th>\n";
                }
            }
        }
        $result .= "</tr>\n";
        $result .= "</thead>\n";

        $result .= "<tbody>\n";
        if ( $ranking_datas ) {
            if ( count( $ranking_datas ) ) {
                foreach ( $ranking_datas as $ranking_data ) {
                    $result .= "<tr>\n";
                    if ( $ranking_data ) {
                        if ( count( $ranking_data ) ) {
                            foreach ( $ranking_data as $cell_data ) {
                                $result .= "<td>" . $cell_data . "</td>\n";
                            }
                        }
                    }
                    $result .= "/<tr>\n";
                }
            }
        }
        $result .= "</tbody>\n";

        $result .= "<tfoot>\n";
        $result .= "<tr>\n";
        $result .= "<th>Ranking</th>\n";
        if ( $subjects ) {
            if ( count( $subjects ) ) {
                foreach ( $subjects as $subject ) {
                    $result .= "<th>" . $subject . "</th>\n";
                }
            }
        }
        $result .= "</tr>\n";
        $result .= "</tfoot>\n";

        return $result;
    }
}