<?

// include
require '../_inc/config.php';

    // cmon baby do the locomo.. validation
    if ( !$data = validate_post() ){
        redirect("back");
    }
    
    extract( $data );

    $slug = slugify($title);
/*  vytvořím si nový data */
    die($slug);

    // $data output control
    echo "<pre>";
    print_r( $data );
    echo "</pre>";

    $update_post = $db->prepare("
        INSERT INTO posts
            (title, text, slug)
        VALUES
            (:title, :text, :slug)
    ");
/*  pokud vkládáme post, potřebujeme vložit
    - title - samotný nadpis
    - text - text
    - slug - ale taktéž musím vytvořit slug
        - musím si ho nějak vytvořit - z title (v funtions-string.php si na to vytvoříme funkci slugify )
*/

    $insert = $update_post->execute([
        "title"   => $title,
        "text"    => $text,
        "slug"    => $slug
    ]);

    // pokud se nepodaří insertnout
    if ( !$insert ){
        flash()->warning("sorry, girl");
        redirect("back");
    }
    // pokud se podaří insertnout 
    $post_id = $db->lastInsertId();

/*  kontrolovat můžeme pokud execute() funkce vrací true nebo false
    - vložím si ji pod $insert
    pokud se mi podaří
    - v první řadě potřebuji id nového příspěvku
        - budu ho potřebovat na např. redirect na samotný nový post
        - pak na vkládání tagů
*/

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
    // lets visit the new post
    flash()->success("yay new one");

    redirect(get_post_link([
        "id"  => $post_id,
        "slug" => $slug,
    ]));
/*  víme, že naše get_post_link funkce pracuje s id a slugem - z toho vytvoří link (post/:id/:slug) na samotný příspěvek */


/*  při editě musím kontrolovat i post id - při přidávání nemámáme id (není co kontrolovat)
    - v rámci validace žádné post id nemáme - musíme upravit validační funkci ve function-post.php
*/