<?php
// show all errors
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

// zobraz všechny errory a warningy - kromě notice erroru
// error_reporting(E_ALL & ~E_NOTICE);

// require stuff (for install use "composer update" into "Command Prompt")
if( !session_id() ) @session_start();
require_once "vendor/autoload.php";

// realpath control
// print_r( realpath(__DIR__ . "/../") ); 

// constants & settings
define( "BASE_URL", "http://localhost/blog" );
define( "APP_PATH", realpath(__DIR__ . "/../") );

// constant control
// print_r( BASE_URL );
// print_r( APP_PATH );

// configurations
$config = [
    
    'db' => [
        'type'     => 'mysql',
        'host'     => 'localhost',
        'database' => 'blog',
        'username' => 'root',
        'password' => 'root',
        'charset'  => 'utf8'
    ]

];
// connect to db
$db = new PDO("mysql:
    host={$config['db']['host']};
    dbname={$config['db']['database']};
    charset={$config['db']['charset']}",
    $config['db']['username'],
    $config['db']['password']
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
try{
    $query = $db->query("SELECT * FROM tags");
    
    // $query output control
    // echo "<pre>";
    // print_r( $query->fetchAll() );

}
catch( PDOException $e ){

    // echo "</pre>";
    $error = date("j M Y, G:i") . PHP_EOL;
    $error .= "---------------------------" . PHP_EOL;
    $error .= $e->getMessage() . "in [ " . __FILE__ . " ] " . PHP_EOL . PHP_EOL;
    // error message edit control
    // echo "<pre>";
    // print_r( $error );
    // echo "</pre>";

    file_put_contents( APP_PATH . "/_inc/error.log", $error.PHP_EOL, FILE_APPEND );

}
// global functions
require_once "functions-general.php";
require_once "functions-string.php";
require_once "functions-auth.php";
require_once "functions-post.php";

require_once("vendor/phpauth/phpauth/Config.php");
require_once("vendor/phpauth/phpauth/Auth.php");

$auth_config = new PHPAuth\Config($db);
$auth   = new PHPAuth\Auth($db, $auth_config);

