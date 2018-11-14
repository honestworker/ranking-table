<?php

    include_once( './include/class.excelmgr.php' );
    
    $excel_mgr = new ExcelManagement();
    $all_category = $excel_mgr->allCategory();
    $subjects = $ranking_datas = array();
    $category_id = 0;
    if ( $all_category ) {
        if ( count($all_category) ) {
            $category_id = $all_category[0]['id'];
            if ( isset($_GET['category_id']) ) {
                $category_id = $_GET['category_id'];
            }
            $subjects = $excel_mgr->getSubjectNames( $category_id );
            $ranking_datas = $excel_mgr->getRankingDataByCategory( $category_id );
        }
    }
?>
<!--================Table View Area =================-->
<section class="projects_area p_120">
    <div class="main_title">
        <h3>Raking View</h3>
        <p>You can see ranking here.</p>
    </div>
    <div class="projects_fillter">
        <ul class="filter list">
            <?php
                foreach ( $all_category as $category ) {
            ?>
                <li <?php if ($category_id == $category['id']) {?> class="active" <?php }?>><a href="./index.php?category_id=<?php echo $category['id'];?>"><?php echo $category['name'];?></a></li>
            <?php
                }
            ?>
        </ul>
    </div>
    <div class="container-center">
        <table id="ranking_table" class="display" style="width:100%">
            <thead id="ranking_table_header">
                <tr>
                    <th>Ranking</th>
                    <?php
                    if ( $subjects ) {
                        if ( count( $subjects ) ) {
                            foreach ( $subjects as $subject ) {
                                ?>                                    
                                <th><?php echo $subject?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody id="ranking_table_body">
            <?php
                foreach ( $ranking_datas as $ranking_data ) {
                    ?><tr><?php
                    foreach ( $ranking_data as $cell_data ) {
                        ?>
                            <td><?php echo $cell_data;?></td>
                        <?php
                    }
                    ?></tr><?php
                }
            ?>
            </tbody>
            <tfoot id="ranking_table_footer">
                <tr>
                    <th>Ranking</th>
                    <?php
                    if ( $subjects ) {
                        if ( count( $subjects ) ) {
                            foreach ( $subjects as $subject ) {
                                ?>                                    
                                <th><?php echo $subject?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</section>