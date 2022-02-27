<?php
    require_once "_inc/config.php";
/*  při vytváření lepší URL by se nám mohla hodit proměnná $_SERVER a zkontrolujeme, zda by se nedalo něco použít
        - např. REQUEST_URI - /blog/delete/2
            - zde nastává problém, že /blog/ je první segment, ale ten se může měnit ¨
                - např. při nahrání na server do rootu složka být nemusí ->  delete bude první segment (nebude fungovat)
            - je potřeba odstranit /blog/ - vytvoříme si funkci get_segments()
*/
    // server information control
    // echo "<pre>";
    // print_r($_SERVER);
    // echo "</pre>";

    // get_segments() function control
    // echo "<pre>";
    // print_r( get_segments() );
    // echo "</pre>";
/*  pokud odstraním base URL, dostanu tu část, kterou můžu rozbít na segmenty - přidám do get_segments() funkce */
    // segment() function control
    // echo "<pre>";
    // print_r( segment(1) );
    // print_r( segment(2) );
    // print_r( segment(3) );
/*  bude prázdné - neexistuje
    - nyní mám způsob jak zjisit co user napsal do adresy
*/
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
/*  cesty nazýváme "routy", to jak s nimi pracujeme nazýváme "routing"
    - pokud bychom chtěli vytvářet nějaký routing system - existuje několik balíčků např. packagist/klein nebo dispatch
    - každý framework má svůj vlastní vymakaný systém pro routing
    nyní vypsané routy budou fungovat, ale nebude fungovat pouze blog/ -> což by nás mělo přesměrovat na index.php
    UPDATE: cesta (key) s jaký soubor se má spustit (value)
        "post" -> "/post" => "post.php"
        - jenže jiné soubory budeme chtít zobrazit, když přijde GET request a jiné když přijde POST request
    UPDATE: pole rout můžeme rozšířit
        "/" => "home.php ->
        "/" => [
            "GET" => "home.php"
        ],
        - rozšížíme kdy key je stále cesta, ale valuu rozšíříme na další pole kde podle metody otevře daný soubor
    nyní potřebujeme získat metodu - pomůže nám proměnná $_SERVER kde najdeme "REQUEST_METHOD"
*/
    $page = segment(1);
    $method = $_SERVER["REQUEST_METHOD"];
/*  $page - víme na jakou stránku se chcme dostat
    $method - získáme metodu jakou dostáváme
    - nyní můžeme podmínku (zda se stránka nachází) změnit
        if ( !in_array( segment(1), $routes ) ) -> if ( !isset( $routes["/page"][$method] ) )
        - pokud se v $routes nenachází "page" ukaž 404, ale stjně tak i pro metodu
        - např. podívám se pokud existuje "/delete" a pokud v něm existuje "GET - pokud ano zobrazí daný soubor
*/

    if ( !isset( $routes["/$page"][$method] ) ){
        show_404();
/*      pokud to, co uživate zapíše do segment(1) se nenachází v $routes zobraz 404 funkci
        - může zapsat jen "post, "edit" a "delete"
*/
    }
/*  pokud existuje, tak si ho requiernem (přihodím soubor, který potřebuji)
    UPDATE: nyní nepotřebuji zobrazovat nic na index.php - zobrazím na ostatních souborech (co mám v $routues)
        - smažu header footer a ostatní html
        - přidám si stránky, které mám $routues -> chybí nám post.php
        - už si budeme moct zobrazit příslušnou adresu podle toho na který soubor se snažím jít (o všem rozhoduje index.php soubor - konkrétně $routes)
*/
    require $routes["/$page"][$method];