<?

// include
require '../_inc/config.php';

    // cmon baby do the locomo.. validation
    if ( !$data = validate_post() ){
        redirect("back");
    }
    
    extract( $data );
/*  to co se mi z validate_post() uloží do data
    - pokud je to false uděláme redirect zpět
    nyní chceme vytáhnout data z naší compact() funkce
    - na to se používá funkce extract()
        - udělá: $post_id = $data["post_id"] atd.;
    nyní potřebuji udělat z edit.php na add.php
    - nakopíruji si a přepíši
*/
    // add new litle and text
    $post = get_post( $post_id, false );
    // add new title and text
    $update_post = $db->prepare("
        UPDATE posts SET
            title = :title,
            text = :text
        WHERE
            id = :post_id
    ");
    $update_post->execute([
        "title"   => $title,
        "text"    => $text,
        "post_id" => $post_id
    ]);
    // remove all tags for this post
    $delete_tags = $db->prepare("
        DELETE FROM posts_tags
        WHERE post_id = :post_id
    ");
    $delete_tags->execute([
        "post_id" => $post_id
    ]);
    // if we have tags, add them
    if ( isset( $tags ) && $tags = array_filter( $tags ) ){
        
        foreach( $tags as $tag_id ){
            
            $insert_tags = $db->prepare("
                INSERT INTO posts_tags
                VALUES (:post_id, :tag_id)
            ");

            $insert_tags->execute([
                "post_id" => $post_id,
                "tag_id"  => $tag_id
            ]);
        }
    }
    // redirect
    if ( $update_post->rowCount() ){
        flash()->success( 'yay, changed it!' );
        redirect( var_dump( get_post_link( $post ) ) );
    }
    flash()->warning('sorry girl');
    redirect('back');

/*  pokud máme chyby - vyskakovací okna
    - viděli jsme funkci filter_var, ale máme i funkci filter_input (je stejná ale určené pro data z input pole, např. INPUT_POST, INPUT_COOKIE atd.)
    - chceme filtrovat "title" a má to být string - rád bych si ho sanizizl (očistěný string)
    - to stejné uděláme s textem
    text a title jsou vyžadované
    - musíme kontrolovat, pokud nemáme title/text (uživatel nezadal nebo dal jen mezery - proto trim) - vyhodíme flash message
    pokud máme vyskakovací okna, tak víme, že máme chyby - redirectnem zpět na post (neodešleme)
    - vypisování erroru se nám strká do home linku - přeskládáme HTML kód v header.php (tam kde flash message robrazujeme0)
    - pokud vyplníme vše, nechá nás to projít
    někde v poli $_POST se nachází položka post id a musí to být inteager
    - přidáme si proměnnou která bude kontrolovat post id či to je int
    - pokud to není inteager (někdo ho smazal, přepsal atd.), vyhodí flash message (nechceme aby někdo přepisoval příspěvek s jiným id)
        - používá se filter_validate_int pro čísla
    ještě sbíráme údaje tagů - filter_input umí zpracovat i pole - budem chtít aby to byl inteager (id příspvěku a id tagu)
    - aby se projela každá položka v poli musíme přidat za validaci "filter_require_array" - způsobí že v tags zůstane jen inteager
    - pokud bych v HTML upravil hodnoty na prázdný value nebo přepsal z čísla na string - neprojde validací (vrátí se prázdné hodnoty - nebyl to inteager)

    pokud chyby nemáme - cheme udělat několik akcí
    - chceme updatovat na nový title a text
    - chceme odstranit všechny tagy, které jsme tam měli doteď (případně následně přidáme nové)
        - potřebujeme mít možnost i odebírat tagy
    - chceme přidat nové tagy (pokd nějaké máme)
    1. chci updatnout posts nastavit nový title, nový text pro posty, které mají toto id
    - pak to executnu a předvyplníme
    2. pak chhci vymazat všechny tagy, které patří tomtuo příspěvku
    - víme, že to editujeme v tabulce posts_tags (tam jsou propojení mezi tým který post ma který tag) - ty které patří tomuto id (WHERE post_id) vymažeme
    3. pokud nějaké tagy máme
    - evidujeme je v proměnné $tags (pole), proběhneme si pole (idček, které máme dát zpět do tabulky)
    - každé id vložíme do tabulky posts_tags (dvojkombinace čísel v databázi - pro který post má přijít který tag)
    nyní si vyzkoušíme, zda funguje - necháme si vrátit succes message a $update_post (vytvořili jsme si query, které updatuje post tabulku)
    - zkontrolujeme si kolik řádků bylo upravených
    UPDATE: v podmínce pokud máme změněné tagy, insertujeme v cykle tagy - pokud bych měl hodně tagů, mohlo by to být náročné na procházení
    - muselo by se hodněkrát projít a udělat hodně insertů (zpomalý) - lepší bude udělat tak, aby se řádky, které se mají vložit vložily jedním insertem
    - to si můžete zkusit udělat
    můžeme si ještě udělat kontrolu, zda je title jiný než ten co se snažím vložit
    - update položky by se dělal jen když je rozdílná
    - další věc, co si můžete zkusit sami (myslete na to že pokud je text jiný a title stejný, musel by se udělat pouze text update a naopak)
    NYNÍ si uděláme redirect na stránku samotného page
    - zkontrolujeme zda se nám podařilo něco změnit (upravili jsme nějaká post)
        - flashneme si succes message a zároveň se redirectneme na samotný page (použijeme naši funkci get_post_link() s paramtrem $post)
    - pokud se nepodařilo hodíme si warning

*/

// $_POST array output control
/* v případě, že odešlu post formulář */
// echo "<pre>";
// print_r( $_POST );
// echo "</pre>";
/*  nejprve data musíme sanizitovat až pak je vložit do databáze 
    - stejně tak musíme zkontrolovat, zda nějaká data máme 
        - pokud nezadá, vyhodíme flash message "chybí název" a vrátíme zpět na post edit
        - zároveň kontrolujeme zda mají správný formát id je int text je string atd. (validace)
*/