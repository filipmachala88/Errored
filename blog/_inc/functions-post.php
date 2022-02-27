<?php


/**
 * Get POST
 * 
 * Tries to fetch a DB post based on URI segment
 * Returns false if unable
 * 
 * @param inteager id of the post to get
 * @return DB post or false
 */
function get_post( $id = 0, $auto_format = true ){
    // if we have no id, or if id is empty
    if( !$id && !$id = segment(2) ){
        return false;
    }
/*  UPDATE: nyní kontrolujeme jestli nějaké id máme v segmentě 2
        if( ! isset($_GET['id']) || empty($_GET['id']) ) ->  if( $id = segment(2) ){
    UPDATE: přidáme id jako argument
        function get_post() -> function get_post( $id = 0 )
        - můžu si vyžádat post s daným id
    UPDATE: pokud žádné id nezadám
            if( $id = segment(2) ) -> if( !$id && $id = segment(2) )
            - tak si ho nechán zjistit z druhého segmentu
*/
    if (! filter_var( $id, FILTER_VALIDATE_INT ) ){
        return false;
    }
/*  naše ID musí být vždy inteager - můžeme si udělat kontrolu
    - prvně si necháme zvalidovat, pokud není inteager vyhodí false
    - tím pádem se ani nedostane ke query (neohrozí)
        - pokud to bude inteager, tak i pomocí execute() se z toho odfiltrují nebezpečné výrazy (žádný SQL injection nehrozí)
    - už máme bezpečně naformátovný POST - můžeme ho vložit do stránky (post.php) jako PHP v HTML
        - jenže náš "text" z datbáze nebude nijak naformátovaný, tak jak ho máme v databázi
        - musíme si pro to vytvořit funkci v functions-string
*/
    global $db;

    $query = $db->prepare( create_posts_query( "WHERE p.id = :id" ) );
/*  do funkce můžu vložit moji funkci, a jako parametr pouze where podmínku
    - provedu stejné pro ostatní prepare() funkce
    - pokud nemám WHERE podmínku, stačí pouze naše funkce
*/
    $query->execute(["id" => $id ]);
/*  oprava proměnné v SELECTU
    - když potřebuji data do query stringu dělá se tak, že:
    UPDATE: 1. přes PDO nejprve připravíme query 2. kam půjde nějaká hodnota
        1. $db->query() -> $db->prepare()
        2. WHERE p.id = $id -> WHERE p.id = :id
    - pak query spustíme přes execute(), kde jako paramtetr bude pole vložené id (:id) a bude mít hodnotu (=>) proměnné $id
    VÝHODA: to naše id (co jsme měli pod $db) je nyní bezpečnější (očistěné od nebezpečného kódu)
    - to naše $id je id v URL - k tomu má uživatel přístup (cokoliv co napíši místo id v URL - to se uloží do proměnné $id)
    - tedy by se to vložilo přímo do SQL dotazu (mohl bych tam naspsat SQL kód, který by se vložil do našeho SQL kódu - člověk může měnit náš kód)
    říká se tomu SQL INJECTION - nejčastější způsob jak někdo může zničit/ukrást stránku nebo vymazat data
        1. způsob: máme post/1/title -> tato část "post/$id"
            - my máme SELECT * WHERE id = 1
        - pokud by tam napsal post/1 OR 1 = 1
            - měli bychom SELECT * WHERE id = 1 OR 1 = 1
            - napsal podmínku: OR 1 = 1 což je vždy, tato podmínka bude platit pro každý jeden řádek -> vypíše všechny řádky databáze
        - to by mohl být problém v případě, že máme data o uživatelech (měl by vidět jen své data), tak ukradl všechny
        - pokud bychom měli nastavený na LIMIT 1, trochu bychom mu to zhrošili (ukradl jen data 1. uživatele)
            - zase by mohl vložit: post/1 OR 1 = 1 -- LIMIT 1 (pomlčka je komentář v SQL)
            - nemusí být limit, můžou být jakékoliv podmínky, které zabraňují se dostat do databáze - nebudou fungovat
        2. způsob: víme, že v SQL středník ukončuje příkaz
            - pokud zadá: post/1; SELECT * FROM tags -> vloží se do id -> vybere všechna data z tabulky tags
                - ukončil první query, zadal druhé query (vlastní), mohl by použít tabulku např. users (vytáhl by data užřivatelů)
            - nebo by zadal post/1; DROP TABLE users - smaže celou databázi o uživatelech
        ALTERNATIVA: pokud bychom chtěli zapsat 1 OR 1 - prohlížeč nám pomůže - nezná mezery, místo nich používá procenta % (mezeru zakóduje na procento)
            - toto zakódování však nemusí udělat každý prohlížeč (pokud to dělá), ani server
                - staré prohlížeče (nechci se spoléhat na to, že to zvládnou)
                - staré servery
            - co když to není z adresy, ale z formuláře (např. zadej věk, místo věku zadá SQL komentář - vytáhne vše)
                - tím pádem se to vloží přímo pod proměnnou $id
        OPRAVDOVÁ ALTERNATIVA: všechny tyto věci se dělají přes tzv. PREPARED STATEMENT
            - vždy když budu potřebovat data poslat do query, tak je použijeme prepared statements
            - nejprve přes prepare() funkci si připravíme query a v ní vyznačíme místa, kam budeme chtít vložit data (nějakým znakem např. otazníkem ? nebo si tyto místa můžu pojemnovat)
                - query se prvně pošle na SQL server, tam se připraví a čeká dokud mu tam nepošleme samotná data
                - posílaní dat děláme přes execute() kde jako pole vložíme data, které se tam mají poslat
                - při použávání se odfiltruje, zakomentuje, zauvozovkuje vše co vypadá jako nebezpečný SQL kód (odstraní se) a spustí se pouze bezpečný kód
                    - pokud zadám 3 OR 1 - zůstane pouze jako 3 (ostatní viděl jako nebezpečný kód, zakomentoval/zauvozovkoval to), viz. post.php komentáře

*/

    if ( $query->rowCount() === 1 ){
        
        $result = $query->fetch( PDO::FETCH_ASSOC );
    
        if( $auto_format ){
            $result = format_post( $result, true );
            /* přidání true pro formátování*/

        } else {
            $result = (object) $result;
        }
    }
    else {
        $result = [];
    }
    return $result;
}
/*  do get_post si můžeme natáhnout funkce z get_posts - je to to samé, jen vytahujeme konkrétní prvek
    - do SELECT z tabulky musíme napsat WHERE p.id = $id  (!$id které jsme si vytáhli nahoře)
        - nikdy NEDĚLAT! víme, že pracujeme s PHP, proto tam můžeme zapsat, ale nikdy nevkládat proměnnou do SELECT
    - fetchAll() změníme na pouze fetch() funkci - chci vytáhnout pouze jeden
    do parametru funkce si hodíme auto format
    - nyní v podmínce auto formátu máme jen jeden příspěvek - nemusíme volat formta post pro všechny - odstraníme array_map()
    - $results už nejsou, je jen $result - vracíme jeden jediný
    OPRAVA:
        if( $id && !$id = segment(2) ) -> if( !$id && !$id = segment(2) )
        - nebude fungovat, funkce je postavená tak, že já můžu vytáhnout post s id 2 (ale nemusím)
            - pokud nezadám žádné tak se id zjistí ze segmentu(2)
        - jenže tato věta před tím znamenala, pokud nemám id ale zároveň se nám podaří získat id ze segmentu 2
            - nemůžu vrátit false - chybí vykřičník !$id
        - má být: pokud nemáme id a zároveň se nepodaří získat id ze segmentu 2 - vrať false
*/
/**
 * Get Posts
 * 
 * Grabs all posts from the DB
 * And maybe formats them too, depending on $auto_format
 * 
 * @param bool|true $auto_format whether to format all posts or not
 * @return array
 */
function get_posts( $auto_format = true){
    global $db;
/*  potřebujeme přístup do databáze - k tomu máme proměnnou $db
    - pokud chceme pracovat s jinou proměnnou než je v parametru funkce - musíme použít global
    - nebezpečné při vkládání jiného kódu do aplikace (můžou být duplikátní) - při větších aplikacích doporučeno nepoužívat (podstupujeme vlastní riziko)
    - v případě, že máme menší aplikaci a kontrolujeme veškerý kód, tak to může být OK
*/
    $query = $db->query( create_posts_query() );
/*  vytáhnutí všech tagů a obsahu k příspěvků
    - jediná změna je přidání LEFT JOIN posts:tags
        - víme, že JOIN vytáhne jen ty řádky, které mají záznam v obou tabulkách
        - to by vytáhlo jen příspěvky, které mají nějaké tagy (ale já chcí všechny příspěvky - ať mají či nemají tagy)
        - proto přidám LEFT JOIN (tabulka posts je levá tabulka - LEFT zaručí, že všechno z levé tabulky tam bude)
*/
    if ( $query->rowCount() ){
        $results = $query->fetchAll( PDO::FETCH_ASSOC );
/*      s datami v PHP se lépe manipuluje polke - má mnoho array funkcí (nemá mnoho objekt funkcí)
        - zároveň ale rád vypisuju jako objekt (můžu použít šipku)
        - proto já si nechám vypsat data jako pole, ale pak si je stejně můžu předělat (castnout) na objekt
*/      if( $auto_format ){
            $results = array_map("format_post", $results);
/*          ve funkci nastavéím jako parametr auto_format jako true - budu moci vypnout přes false (kdybych nechtěl vždy formatovat)
            - pokud se mi podaří vytáhnout příspěvky (musí být v podmínce $query->rowCount())
            - tak zkontroluji, zda je auto_format nastavený na true (podmínka)
                - pokud ano - každý jeden prvek v poli ($results) a nechám projít přes funkci "formta_post" (parametr)
                - to následně hodím zpět do proměnné $results
            přidáme komentář k funkci
*/
        }
    }
    else {
        $results = [];
    }
    return $results;
/*  dobrý nápad je kontrolovat, jestli se nám podařilo vytáhnout něco z naší query
    - můžeme přes funkci rowCount()
        - pokud vrátí nějaké číslo (větší jak 0 - vrátí se nějaké řádky)
        - tak si pod $results nechám fetchnout (vypsat) data jako objekty
        - bude plné pole
    - v opačném případě (němám výsledek)
        - bude prázné pole
    - pak si pole nechám vráítt
*/
}
/**
 * Get Posts By Tag
 * 
 * Grabs posts that have $tag from the DB
 * And maybe formats them too, depending on $auto_format
 * 
 * @param string        $tag
 * @param bool|true     $auto_format whether to format all the posts or not
 * @return array
 */
function get_posts_by_tag( $tag = "", $auto_format = true ){
/*  rozdíl je že zde budu operovat s tagem - což bude string */
    if( !$tag && !$tag = segment(2) ){
/*  zde se nejprve budeme snažit získat tagy z druhého segmentu URL*/
        return false;
    }
    $tag = urldecode( $tag );
    $tag = filter_var( $tag, FILTER_SANITIZE_STRING );
/*  necháme ho dekódovat (od nežádoucích znaků) - protože ho bereme z URL
    - nechám sanitizovat
*/
    global $db;

    $query = $db->prepare( create_posts_query( "WHERE t.tag = :tag" ) );
/*  potřebuji změnit podmínku - už neberu podle id postu ale t.tag */
    $query->execute(["tag" => $tag ]);

    if ( $query->rowCount() ){
/*  očekáváme, že by jich mohlo být více - necháme jen rowCount()
    - tato část může být vzatá z get_posts funkce, kde se snažíme vytáhnout array všech tagů, které plní podmínku
    - z funkce get_post vezmeme vršek a z get_posts vezmeme spodek funkce
*/
    $results = $query->fetchAll( PDO::FETCH_ASSOC );
        if( $auto_format ){
            $results = array_map("format_post", $results);
        }
    }
    else {
        $results = [];
    }
    return $results;
}
/**
 * Create Posts Query
 * 
 * Put together the qury to get posts
 * We can add WHERE conditions too
 * 
 * @param  string $where
 * @return string
 */
function create_posts_query( $where = "" ){
    $query = "
        SELECT p.*, GROUP_CONCAT(t.tag SEPARATOR '-||-') AS tags
        FROM posts p
        LEFT JOIN posts_tags pt ON (p.id = pt.post_id)
        LEFT JOIN tags t ON  (t.id = pt.tag_id)
    ";
/*  zde musí zůstat, vytváříme funkci */
    if ( $where ){
        $query .= $where;

    }
    $query .= " GROUP BY p.id";
    
    return trim( $query );
}
/*  funkce pro query, bude akceptovat $where ale nemusí
    - začneme si vytvářet query, kde bude část, co se neopakuje, co se opakuje (WHERE a GROUP BY) dáme bokem
    - pokud máme nějaké where (pošle se do funkce) - přilepí se na konec query
    - naši query ukončíme group by
        - dáme is mezeru před GROUP BY z toho důvodu, že pokud se pošle bez mezery, tak ať to není nalepené přímo na sobě (where a group by)
    - nyní můžeme vrátit $query a dokonce i trimnout
    teď můžeme nahradit query v ostatních funkcích za naši funkci
    - nyní máme připravené v případě, že bychom chtěli zobazit příspěvky konkrétního uživatele - pouze zmeníme parametr naší funkce (s WHERE query)
*/

/**
 * Format Post
 * 
 * Cleans up, sanitizes, formats and generally prepares DB post for displaying
 * 
 * @param $post
 * @return object
 */
function format_post( $post, $format_text = false ){
    // trim dat shit
    $post = array_map("trim" , $post);

    // $post output control
    // echo "<pre>";
    // print_r( $post );
    // echo "</pre>";

/*  jedna výhoda pro práci s polem je, že si pro každou položku odříznu nadbytečné znaky */
    // celean it up
    $post["title"] = plain( $post["title"] );
    $post["text"]  = plain( $post["text"] );
    $post["tags"]  = $post["tags"] ? explode("-||-", $post["tags"] ) : [];
    $post["tags"]  = array_map("plain", $post["tags"]);
/*  pro kontrolu si vložím do souboru HTML <article> veškerá obsah (lépe uvidím, s čím parcuji - co změním)
    - vím, že vypisuje title a text a taky že je chci pro jistotu proběhnout přes plain() funkci
    - tagy jsou jako string a jsou oddělené spicálním znakem - já však chci mít pole
        - jenže však tagy mít nemusím, proto musím udělat podmínku
            - pokd mám, nechám je rozbít (ze stringu) přes explode() na daný znak (na pole)
            - v opačném případě máme prázdné pole
        - v případě, že je budu vypisovat do stránky je chci nechat proběhnout přes plain()
            - přes array_map nechám provést plain() funkci pro všechny prvky v poli tagů

*/
    // tag me up
    if ( $post["tags"] ) foreach ( $post["tags"] as $tag ){
        $post["tag_links"][$tag] = BASE_URL . "/tag/" . urlencode($tag);
        $post["tag_links"][$tag] = filter_var( $post["tag_links"][$tag], FILTER_SANITIZE_URL );
    }

/*  vyrobíme si linky z tagů
    1. chci aby pod názvem byly tagy (na homepage) vypsané + aby každý tag měl svou vlastní podstránku (s příspěvky daného tagu)
    1. pokud nějaké vůbec máme, tak pro každý z nich jako tag chci vytvořit nějaký nový záznam do "tag_link"
    1. pro tento tag vytvoříme URL složením BASE URL "/tag" a tag (nestačí - mezery se musí přeměnit na nějaký symbol - k tomu slouží urlencode() funkce)
    2. celé to projdeme sanitací (kvůli bezpečnosti - abychom z databáze nevypisovali scripty - dvojitá ochrana, data in data out)
    - nyní můžeme zkontrolovat, co máme v tag_links v HTML (home.php)

*/  
    // create link to post [ /post/:id/:slug ]
    $post["link"] = get_post_link( $post );
    // lets go on some dates
    $post["timestamp"] = strtotime( $post["created_at"] );
/*  zde nepracujeme s objektami, ale s poli - tomu taky musíme přizpůsobit zápis
    - $post->created_at zmeníme na $post["created_at"]
*/
    $post["time"] = str_replace(" ", "&nbsp;", date("j M Y, G:i", $post["timestamp"]));
    $post["date"] = date("Y-m-d", $post["timestamp"]);
/*  nyní zpracování času
    - budu potřebovat "timestamp" (budu s ním pracovat často) - vytvořím přešs strtotime() funkci
    - chci si vytvořit čas "time" - přes date() funkci, paramtery formát a "timestamp"
    - nyní čas pro čtečky (<date> atribut) - to stjené s jiným formátem
    s responzivitou se bude datum rozbíjet - nechceme
    - můžeme použít str_replace() funkci a vzniklé mezery nahradíme nezalomitelnými (non breaking space) mezrami
        - můžeme použít i pro lomítko HTML zankem &nbsp; (datum odskočí společně s lomítkem)
*/
    // dont tease me bro
    $post["teaser"] = word_limiter($post["text"], 40);
/*  nyní vytvoření teaseru
    - použiji naši funkci word_limiter() a omezíme na 40 znaků
    nyní máme vše (jsme hotovi) - HTML <article> můžeme z souboru smazat a následně naše proměnné použít v samotném HTML (pročistíme a nahradíme)
*/
    // formta text
    if ( $format_text ){
        $post["text"] = filter_url( $post["text"] );
        $post["text"] = add_paragraphs( $post["text"] );
    }
/*  přidám odstavce a linky - použiju funkce
    - tyto funkce (filtry) používám jen na aktuálním příspěvku, nikde jinde je nepoužívám (volají se zbytečně) + zbytečně zpomalují načítání kódu
    - v samotné funkci format_post představíme $format_text na false, ale dáme podmínku - pokud je true, tak proveď
        - můžeme si zpřehlednit filtrování - rozdělíme si na 2 jednotlivé funkce (nejprve linky, pak odstavce)
    - do functions-post musíme zapsat $format_post jako true - v tom jediném případě chceme formátovat
*/
    return (object) $post;
}
/**
 * Get Post Link
 * 
 * Create link to post [ /post/:id/:slug ]
 * 
 * @param array|object  $post post to create link to
 * @param string        $type if its a post /edit/delete link
 * @return mixed|string 
 */
function get_post_link( $post, $type = 'post' ){

    if ( is_object($post) ){
        $id = $post->id;
        $slug = $post->slug;
    }
    else{
        $id = $post['id'];
        $slug = $post['slug'];
    }

    $link = BASE_URL . "/$type/$id";

    if ( $type === 'post' ){
        $link .= "/$slug";
    }

    $link = filter_var( $link, FILTER_SANITIZE_URL );
/*  pokud je to objekt, budeme potřebovat 2 věci na vytvoření linku - id a slug
    - pokud máme array (v opačném příapdě), tak chci taky id a tag, ale budeme k tomu přistupovat jako k poli
    nyní vypíšeme jako formát adresy a je to link, tedy chceme zkontrolovat (saniziznout) jako link
    - necháme si to vrátit jako $link
    přidáme atribut, pro možnost jestli to bude na zobrazení/edit/delete
    - víme, že slug se používá jen když je to zobrazení postu
    - $type si necháme vypsat místo BASE_URL . "/post/$id" -> bude tam buď post/edit/delete
    - ale slug potřebujeme jen v situaci, kdy zobrazujeme link na stránku postu (odstráníme z $link a dáme do podmínky "post")
    nyní bych mohl chtít mít link (v post.php), který ho (příspěvek) bude editovat
    - přidáme si do HTML a zavoláme funkci s druhým parametrem "edit"
*/
    return $link;
    
    // $post["link"] = BASE_URL . "/post/{$post['id']}/{$post['slug']}";
    // $post['link'] = filter_var( $post['link'], FILTER_SANITIZE_URL );
/*  přidám si nový list (věc) do array jako $post["link"]
    - to bude BASE_URL spojená s /post/ id /slug
    - pročistíme si link (URL) - aby z toho odešli všechny znaky co nemají být v linku
*/
}
/*  funkce na vytvoření edit linku
    - bude akceptovat $post a z něho vytvoří link
    - budu ho chtít volat v edit.php (abych se dodstal zpět na stránku postu) a v format_posts() funkci jako $post["link"]
        - jenže v tomto případě je to array, naše fuukce by se měla vyspořádat s tím, ať už pošlu array nebo objekt
        - není to nejlepší způsob (zda by se to reálně takto dělalo - aby umělo příjmat oboje), ale je běžnou věcí zjišťovat jaké jsou věci typu
            - podle toho se s nimi vyspořádávat
*/
/**
 * Get Edit Link
 * 
 * Create link to edit post [/edit/:id]
 * 
 * @param $post
 * @return mixed|string
 */
function get_edit_link( $post ){
    return get_post_link( $post, "edit" );
}
/**
 * Get Delete Link
 * 
 * Create link to edit post [/delete/:id]
 * 
 * @param $post
 * @return mixed|string
 */
function get_delete_link( $post ){
    return get_post_link( $post, "delete");
}

function get_all_tags( $post_id = 0 ){
    global $db;

    $query = $db->query("
        SELECT * FROM tags
        ORDER BY tag ASC
    ");

    $results = $query->rowCount() ? $query->fetchAll( PDO::FETCH_OBJ) : [];

    if ( $post_id ){
        $query = $db->prepare("
            SELECT t.id FROM tags t
            JOIN posts_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = :pid
        ");
        
        $query->execute([
            "pid" => $post_id
        ]);
/*      musí být pod if($post_id) */

        if ( $query->rowCount() ){
            $tags_for_post = $query->fetchAll( PDO::FETCH_COLUMN );

            foreach( $results as $key => $tag){

                if ( in_array( $tag->id, $tags_for_post ) ){
                    $results[$key]->checked = true;
                }
            }
        }
    }

    // $query output control
    // echo "<pre>";
    // print_r( $results );
    // echo "</pre>";
/*  dostaneme pole s objektami id, které by měli patřit pro daný příspěvek
    - jenže já chci pouze hodnoty (např. 1 a 3 vráceé v poli) - můžeme si fetchnout jako sloupce (PDO::FETCH_COLUMN)
    - nyní bych všechny tagy co mám vypsané mohl projít a pro ty, které se nachází v tomto poli (co teď kontrolujeme), ty se označí jako "checked"
    pokud příspěvek má nějaké tagy, tak si pole idček ($query->fetchAll() atd.) uložím do proměnné tags_for_post
    - nyní vím, že v $results mám uložené všechny tagy - nechám je proběhnout jako jednotlivý tag a budu kontrolovat
    - pokud se tag id nachází v poli $tags_for_post (patří tomuto postu)
        - přihodím této položce v poli results, že checked má být true - nechám si vypsat $results
    NYNÍ se vypíše pole objektů i s hodnotou [checked] => 1 (pokud mu náleží), pokud ne nevypíše se nic
    - v editačním formulíáři (edit.php - tam kde tagy vypisuji) si udělám kontrolu
    mohlo by být neefektivní v případě, že mám na stránce hodně tagů - probíhání přes pole a kontrolovat jestli tam patří by mohlo trvat
    - dalo by se zamýšlet nad tím, jestli by se to dalo udělat jen pomocí query (přes SQL) - zatím nemusíme, zatím můžeme zajisit, aby tagy byly ve výpise uspořádané abecedně
        - do $query kde vytahujeme všechny tagy (SELECET *) můžeme přidat ORDER BY
    - jelikož máme editační formálář, potřebujeme zajisit abychom si přeposlali informaci o tom, který blog post (id) se snažím upravit
        - stále si musím přeposíla údaje - takto funguje formulář
        - dělá se pomocí hidden inputu -> přidám si do edit.php
*/

    return $results;

}
/*  vytvoříme si funkci na získání tagů
    - vytáhne všechny tagy
    - pokud se nějaké podařilo vytáhnout (existují), tak jich vrátí jako objekt - pokud nepodařilo, vrátí prádzné pole
    v edit.php pod textarea si můžeme vypsat

    UPDATE: chci přidat boolean, zda tagy postu patří (aby byly předvyplněné)
    - funkce bude akceptovat id - bude volitelné, přednastavím na 0
    - pokud jsem zadal nějaké id (tak půjdeme upravit $results array - řádek výše)
    nyní potřebujeme poslat id - už víme, že ho tam nemůžeme vložit jako proměnnou - použijeme prepared statement
    - chcí získat všechny id z tagů (SELECT) a takžéž je musím napojit na tabulku posts_tags (JOIN) protože chci vytáhnout ty tagy, které patří tomu příspěvku
        - tedy ty tagy (WHERE), které post id je post id
    nyní executneme
    POZOR: potřeba doplnit parametr v "edit.php" do get_all_tags() funkce jako $post->id
*/

/**
 * Valitade Post
 * 
 * Sanitize and validate, sister
 * 
 * @return array|bool
 */
function validate_post(){
    
    $title = filter_input( INPUT_POST, "title", FILTER_SANITIZE_STRING );
    $text = filter_input( INPUT_POST, "text", FILTER_SANITIZE_STRING );
    $tags = filter_input( INPUT_POST, "tags", FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

    if( isset( $_POST["post_id"] ) ){
        $post_id = filter_input( INPUT_POST, "post_id", FILTER_VALIDATE_INT );
        // id is required and has to be int
        if ( !$post_id ){
            flash()->error("what are you trying to pull here");
        }
    }
    else{
        $post_id = false;
    }
/*  kontrolujeme zda máme nějaký post id zadané
    - pokud ano tak si ho sanitizneme a zkontrolujeme datový typ
    - pokud ne nastavíme si na false (aby se nám v returnu nerozbil compack() )
*/
    // title is required
    if ( !$title = trim($title) ){
        flash()->error("yout forgot your title dumy");
    }

    // text is required
    if ( !$text = trim($text) ){
        flash()->error("write some text, come on");
    }

    // if we have error messages, validation didnt go well
    if ( flash()->hasMessages() ){
        return false;
    }

    return compact($post_id, $title, $text, $tags, 'post_id', 'title', 'text', 'tags');
}

/*  Přidáme si přidávání příspěvků - ještě nemáme
    - prvně ale potřebujeme vytviřit funkci na sanitizaci (uživatel bude moc zadávat data)
    - budeme v ní dělat všechny sanizizační věci, které děláme v "edit-item.php" souboru - nakopírujeme sem
    v rámci této validáční funkce možná nebudu chtít dělat přímo redirect
    - místo toho pokud budou nějaké flash messages (validace se nepodaří), budu chtít udělat return false
    - pokud se podaří, budu chtít vrátit odfiltrovaný post id, title, text a tagy
        - jak můžu returnout více dat zároveň? můžu udělat pomocí pole
        - k tomu slouží funkce compoact, kde vyjmenuji všechny klíče a následně i jejich hodnoty
    nyní v edit item chchi zavolat tuto validační funkci

*/