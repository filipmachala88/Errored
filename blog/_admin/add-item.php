<?

// include
require '../_inc/config.php';

    // validation
    if ( !$data = validate_post() ){
        redirect("back");
    }
    
    extract( $data );

    $slug = slugify($title);
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