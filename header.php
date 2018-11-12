<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="assets/images/favicon.png" type="image/png">
        <title>University Ranking View</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.css">
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
        <!-- main css -->
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        <!-- custom css -->
        <?php
            if (isset( $import_csss )) {
                foreach ( $import_csss as $import_css ) {
                    ?><link rel="stylesheet" href="assets/css/<?php echo $import_css->dir;?>/<?php echo $import_css->name;?>.css"><?php
                }
            }
        ?>
    </head>
    <body>