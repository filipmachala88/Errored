<?php

// global functions
function show_404(){
    header("HTTP/1.0 404 Not Found");
    include_once "404.php";
    die();
}
/*  UPDATE: přejmenujeme funkci - budeme chtít vyáthnout item ne post
    function get_items() -> function get_post()
*/
function is_ajax(){
    return ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' );
}
/**
 * @param $path
 * @param string $base
 * @return string
 */
/* vytvoříme si komentář pro popis funkce - např. PHP storm umí doplňovat do funkcí a jaké má mít parametry */
function asset( $path, $base = BASE_URL . '/assets/'){
/*  parametry: funkce bude brát cestu, a naši konstantu -> bereme, že se soubory nachází v assets - pokud ne, potřeba kontrolovat */
    $path = trim( $path, '/' );
    return filter_var( $base.$path, FILTER_SANITIZE_URL);
    /*  naše konstanta + cestu kterou uživatel napíše (ale odstraníme z ní lomítka)
            - všechny vstupy od uživatele je potřeba filtrovat (tzv. "sanitizace" inputů) - nemůže si uživatel napsat vše
            - můžeme udělat pomocí funkce filter_var()
                - vždy používejte, pokud uživatel vkládá hodnoty (nikdy nevěřte tomu co uživatel zadá)
                - přečíst: https://phptherightway.com/#data_filtering sekce Security
            - pomocí "validate filters" můžeme kontrolovat, jestli je email, web adresa atd. (viz. dokumentace)
            - my však budeme používat "sanizize filters" - očistěný string
            UPDATE: odstřihneme lomena z obou stran, necháme pročistit přes funkci (aby očistil to co nevypadá jako adresa)
                $path = return trim( $path, '/' ) ->    $path = trim( $path, '/' );
                                                        return filet_var( $base.$path, FILTER_SANITIZE_URL);
            - výsledek bude stejný, ale víme, že už se kontroluje  
    */
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
/*  prvně je potřeba dostat localhost/blog/
    - to už máme v BASE_URL, ale ukážeme si, jak by se to dalo dostat z $_SCRIPT údajů
        - víme, že stránky můžou být na šifrovaným (https) nebo nešifrovaným (http) serveru
            - pokud by byl šifrován, přibyde údaj HTTPS => on (klíč, nastaven na hodnotu on)
            - tím zjistíme, jak začíná naše adresa
        - pak přidáme HTTP_HOST
        - přidáme REQUEST_URI
    - dostaneme kompletní adresu stránky, kde se nacházíme
*/
    $current_url = "http" .
        ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "s://" : "://" ) .
        $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];


        $path = str_replace( BASE_URL, "", $current_url );
        $path = trim(parse_url($path, PHP_URL_PATH), "/");
        $segments = explode("/", $path);
/*      odstranění BASE_URL z URL
        - var_dump() - vypíše výsledek včetně kód podrobností
        - str_replace() - chci BASE_URL nahradit prázdným stringem, pokud se nachází v current_url
        nyní vím, co vrací - můžu odstranit var_dump() a vložit si to do proměnné
        
        získání segmentů
        - pomocí parse_url() funkce vytáhne z URL (nějaký úsek), to co potřebujeme (např. port atd.)
        - rozbijeme na segmenty pomocí explode() podle lomítek
        - nyní inexově získáme jednotlivé segmenty, ale první segment bude prázdný (kvůli lomítku na začátku)
            - můžeme použít trim() - všechny nadbytečné lomítka z obou stran půjdou pryč
        UPDATE: nyní chci aby funkce vracela $segments (upravené $current_url)
            return $current_url -> return $segments
            - nyní pokud dám delete/2 -> dostanu: [0] => delete [1] => 2 - vím, co udělat a kde
*/
        // $segments control
        // echo "<pre>";
        // print_r( $segments );
        // echo "</pre>";
        return $segments;
/*      prvně vrátíme http a k tomu :// nebo https a k tomu ://
            - isset() - pokud existuje taková hodnota, vytáhneme si pomocí $_SERVER["HTTPS"] a zároveň je nastavené na "on"
            - ? pokd ano (jsme na šifrovaném serveru) přidáme s:// : pokud ne přidáme jen //
        pak přidáme HTTP_HOST a k němu REQUEST_URI
*/
}
function segment( $index ){
    $segments = get_segments();
    return isset( $segments[ $index-1] ) ? $segments[ $index-1 ] : false;
/*  např. zadám, že chci segment(1)
    $segments - zjistím všechny segmenty -> vrátí pole
    isset - zkontroluji zda segment existuje
    ? - pokud existuje, tak ho vrátím, pokud ne vrátí false
    -1 - v naší aplikaci je 1. segment jako nultý segment a 2. segment je jako první (pokud chci segment 1 ve skutečnosti chcem nultý - jak počítá člověk)
*/
}
function redirect( $page, $status_code = 302 ){
    
    if ($page == 'back')
    {
        $location = $_SERVER["HTTP_REFERER"];
    }
    else
    {
        $page = ltrim($page, '/');
        $location = BASE_URL . "/$page";
        /*  
            V config.php jsme vytvořili konstanty - můžeme je použít
            - už neputřebujem global $base_url - můžeme smazat
            - UPDATE: $location = "$base_url/$page" -> $location = BASE_URL . "/$page";
            - stejně tak v index.php hlavičce používáme $base_url -> přepsat
        */
    }

    header("Location: $location", true, $status_code);
    die();
}