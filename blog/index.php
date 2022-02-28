<?php
    require_once "_inc/config.php";

    // server information control
    // echo "<pre>";
    // print_r($_SERVER);
    // echo "</pre>";

    // get_segments() function control
    // echo "<pre>";
    // print_r( get_segments() );
    // echo "</pre>";¨

    // segment() function control
    // echo "<pre>";
    // print_r( segment(1) );
    // print_r( segment(2) );
    // print_r( segment(3) );
    // echo "</pre>";
    
    $routes = [
        // HOMEPAGE
        "/" => [
            "GET" => "home.php"
        ],
        // POST
        "/post" => [
            "GET"  => "post.php",             // show post
            "POST" => "_inc/post-add.php",    // add new post
        ],
        // TAG
        "/tag" => [
            "GET"  => "tag.php",             // show posts for tag
        ],
        // EDIT
        "/edit" => [
            "GET"  => "edit.php",             // edit form
            "POST" => "_inc/post-edit.php",   // store new valeus
        ],
        // DELETE
        "/delete" => [
            "GET"  => "delete.php",           // delete form
            "POST" => "_inc/post-delete.php", // make the delete
        ],
    ];

    $page = segment(1);
    $method = $_SERVER["REQUEST_METHOD"];

    if ( !isset( $routes["/$page"][$method] ) ){
        show_404();
    }
    require $routes["/$page"][$method];