<?php
// show all errors
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

// require stuff (for install use "composer update" into "Command Prompt")
if( !session_id() ) @session_start();
require_once "vendor/autoload.php";
/*
    čím méně globálních proměnných, tím lépe
    - proto často lidé vytváří jeden config array, do kterého si nahážou všechna nastavení
    - my tam můžeme dát "base_url" nebo "database" (pokud bychom používali medoo)

    podobně jako máme absolutní cestu pro URL adresu by bylo mít dobré absolutní cestu i na disk PC
    - na to používáme:
        - __DIR__ = adresář disku (složka) v kterém se nachází tento soubor (localhost/blog/_inc)
            - pokud by se nám podařilo dostat o jeden adresář výše (např. jak u cd..), tak máme adresář složky ve kterém je naše aplikace
            - to se dá dosáhnou pomocí funkce realpath(), přidám "/../" -> vrátí o adresář výše (localhost/blog)
        - __FILE__ = cesta k souboru samotnému (localhost/blog/_inc/config.php)

    "base_url" ani "app_path" se nebudou během tvorby aplikace měnit - proto je můžeme použít jako
    KONSTANTA - vytvoří se jednou (pak se nedají změnit), jsou globální (nemusíme používat příkaz global)
    - vytváří se pomocí definování - k tomu slouží funkce define() - s paramtery: ("název", "hodnota")
*/

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
/*  komunikace v PHP se dělá přes tzv. PDO (PHP data objects) - není to jediný způsob, ale je doporučený
    - má systém, které nám pomáhají s bezpečností
    - nabízí ucelený interface pro různé implementace SQL (můžeme používat ty stejné funkce pro MySQL, SQL Lite, postSQL)
    - viz. https://code.tutsplus.com/tutorials/why-you-should-be-using-phps-pdo-for-database-access--net-12059
    UPDATE: vezmeme PDO funkci a změníme na naše nastavení databáze (pokud nehodí error - jsme OK), původní:
        $db = new PDO('mysql:host=localhost;dbname=testdb;charset=utf8', 'username', 'password');
    UPDATE: $config můžeme změnit názvy z zkratky: např. database -> db
        - nahradíme "root" v PDO za data v $config + ostatní
*/
$db = new PDO("mysql:
    host={$config['db']['host']};
    dbname={$config['db']['database']};
    charset={$config['db']['charset']}",
    $config['db']['username'],
    $config['db']['password']
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
/*  nastavování attributů
    - ATTR_EMULATE_PREPARES - PDO (objekt, který používáme na komunikaci s databází) připravuje SQL kód před tím, než ho pošle do databáze
        - připravuje kód před tím než pošle query (kvůli rychlosti nebo bezpečnosti atd.)
        - PDO umí připravit kód - ale to je potřebné jen při starších verzích SQL (4.0)
        - nové verze si SQL kód umí připravit sami od sebe (nemusíme se spolíhat na PDO a jeho PHP funkce)
        - ATTR_EMULATE_PREPARES je defaultně nastavené na hodnotu true, proto je potřeba přidat FALSE (true by mělo smysl pouze při staršich verzích SQL)
    - ATTR_ERRMODE - co se má stát, když uděláme SQL chybu (např. SELECT FROM tabulka která neexistuje)
        - nyní nastaveno na:
            - ERRMODE_EXCEPTION - vyhodí PHP chybu "fatal error" dále NEVYPÍŠE
                - nabízí "Uncaught exception" error (nezachycená výjimka) - dává možnost vyspořádat se s problémy, tak jak chceme
                - za použití "Try catch" bloku
                    - try: zkus,
                    - catch: v případě že to zlíhá (s errorem), můžeme to zachytit (tu tzv. výjimku/výhradu)
                        - výjimky mají různé typy, musíme napsat jakou chceme zachytit (v našem případě PDOException - viz. vyhozený error)
                    - používá se ve více programovacích jazycích
        - je jich více: 
            - ERRMODE_SILET - nic se nestane (nic nevyhodí)
            - ERRMODE_WARNING - vyhodí PHP chybu, ale dále VYPÍŠE (program pokračuje)
                - během vývoje chceme vypsat, během živé stránky necheme, aby uživatel viděl 
                - 1. běžné uživatele to nezajímá, 2. hackery to zajímá - může vidět náš kód, co jsme se snažili udělat (kde můžou být údaje o databázi)
*/
try{
/*  zkus provést query */
    $query = $db->query("SELECT * FROM tags");
    
    // $query output control
    // echo "<pre>";
    // print_r( $query->fetchAll() );
/*  zkus vypsat všechny data
    - nechceme aby aplikace přestala fungovat v případě chyby
*/
    // echo "</pre>";
/*  PDO nám dává možnost přistupovat ke klíči a hodnotě pro každý prvek (klíč i index)
    - máme dvojnásobek hodnot - je potřeba si vybrat jeden způsob
    - můžeme přistupovat přes název - $tag["id"] nebo $tag["title"], nebo přes index $tag[0]
        [0] => Array
            (
                [id] => 1     [0] => 1
                [tag] => akce [1] => akce
            )
    UPDATE: musíme si vybrat co budeme fetchovat (jaký způsob si vybereme)
        PDO:FETCH_NUM - numerické čísla
        PDO:FETCH_ASSOC - asociativní pole (id a tag)
        PDO:FETCH_OBJ - vrátí se jako objekt
    - v reále budeme chtít objekt nebo assoc
        $query->fetchAll() -> $query->fetchAll( PDO:FETCH_NUM )

*/
}
catch( PDOException $e ){
/*  pokud se ti nepodaří, vypiš co se tí nelíbí
    - vyhodí objekt s několika údaji tzv. Metodami (metody - funkce prilepene na objekt) - nejvíce nás zajímá messsage:protected (co je za chybu)
*/
    // error message control
    // echo "<pre>";
    // print_r($e->getMessage() );
/*  vrátí pouze message z PDOException objektu
    - umíme získat chybu jako string a s ním můžeme pracovat
    - aniž bychom ji vypsali na obrazovku (s tím, že aplikace pořád funguje)
    - chceme ji však někam zachytit, např. do souboru (který pak můžeme otevřít a chybu začít řešit)
*/
    // echo "</pre>";
    $error = date("j M Y, G:i") . PHP_EOL;
    $error .= "---------------------------" . PHP_EOL;
    $error .= $e->getMessage() . "in [ " . __FILE__ . " ] " . PHP_EOL . PHP_EOL;
    // error message edit control
    // echo "<pre>";
    // print_r( $error );
    // echo "</pre>";
/*  1. $error = poznačíme si, kdy (datum J M Y a čas G.i) chyba nastala - odentruji 
    2. $error = vytvoříme si oddělovat - odentruji
        - spojením tečky a rovnáse viz. .= na string předchozí se nalepí stávající string (bez tečky bychom přepislovali)
    3. $error = vypíšeme si chybu a kde nastala chyba (soubor) - 2x odentruji (např.)
*/
    file_put_contents( APP_PATH . "/_inc/error.log", $error.PHP_EOL, FILE_APPEND );
/*  file_Putus_content - funkce na zapsání
        - dobrá funkce, protože: prvně zkontroluje zda soubor existuje (pokud ano - přidá string na konec, pokud ne - tak ho vytvoří)
    APP_PATH . "/_inc/error.log" - kam chci zapsat (nyní absolutní cesta) - vytvoří se nám error.log soubor do kterého zapíšeme
    $error - obsah error zprávy
    FILE_APPEND - způsobí, že funkce file_puts_content() se nebude soubor stále přepisovat, ale novou zprávu přidá na konec souboru
    
*/
}
// global functions
require_once "functions-general.php";
require_once "functions-string.php";
require_once "functions-post.php";
/*  budou nám přibývat funkce - nechceme to mít v jednom dlouhém souboru "functions.php" - můžeme si vytvořit další soubory (tématicky)
    - "functions-general" - 404, segment, redirect
    - "functions-string"  - funkce pro formátování textu
    - "functions-post"    - vypisování příspěvku
*/

/*  nechceme mít URL edit.php?=3, chceme radši localhost/blog/edit/3
        - nedá se dosáhnout pomocí PHP, ale přes nastavení serveru (dá se ovlivnit přes .htaccess soubor)
    - funguje tak, že máme localhost/blog/index.php/edit/4 jen index je schovaný
    - přesměrujeme podle toho, co zůstane za ním (všechny adresy budou směrovat na index.php)
        - pomocí request_uri zkontrolujeme co vše se nachází v adrese a podle toho přesměrujeme na patřičnou podstránku
        - přesměrování na podstránku index.php se dělá pomocí .htaccess souboru (v případě apache serverů)
            - rozebírání .htaccess souboru je na dlouho (vezmeme předvytvořený z PHP framewroku)
*/

