<?php

// global functions
function show_404(){
    header("HTTP/1.0 404 Not Found");
    include_once "404.php";
    die();
}
function is_ajax(){
    return ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' );
}
/**
 * @param $path
 * @param string $base
 * @return string
 */
function asset( $path, $base = BASE_URL . '/assets/'){
    $path = trim( $path, '/' );
    return filter_var( $base.$path, FILTER_SANITIZE_URL);
  
}
/**
 * Get Segments
 * 
 * From a url like https://example.com/edit/5
 * it creates an array of URI segments [ edit, 5 ]
 * 
 * @return array
 */
function get_segments(){

    $current_url = "http" .
        ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "s://" : "://" ) .
        $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];


        $path = str_replace( BASE_URL, "", $current_url );
        $path = trim(parse_url($path, PHP_URL_PATH), "/");
        $segments = explode("/", $path);
        // $segments control
        // echo "<pre>";
        // print_r( $segments );
        // echo "</pre>";
        return $segments;

}
function segment( $index ){
    $segments = get_segments();
    return isset( $segments[ $index-1] ) ? $segments[ $index-1 ] : false;

}
/**
 * Redirect
 *
 * @param $page
 * @param int $status_code
 */
function redirect( $page, $status_code = 302 )
{
    if ( $page == 'back' )
    {
        $location = $_SERVER['HTTP_REFERER'];
    }
    else
    {
        $page = str_replace( BASE_URL, '', $page );
        $page = ltrim($page, '/');

        $location = BASE_URL . "/$page";
    }

    header("Location: $location", true, $status_code);
    die();
}