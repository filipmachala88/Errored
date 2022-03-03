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

    if (! filter_var( $id, FILTER_VALIDATE_INT ) ){
        return false;
    }

    global $db;

    $query = $db->prepare( create_posts_query( "WHERE p.id = :id" ) );

    $query->execute(["id" => $id ]);

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
    $query = $db->query( create_posts_query() );
    if ( $query->rowCount() ){
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
    if( !$tag && !$tag = segment(2) ){
        return false;
    }
    $tag = urldecode( $tag );
    $tag = filter_var( $tag, FILTER_SANITIZE_STRING );
    global $db;

    $query = $db->prepare( create_posts_query( "WHERE t.tag = :tag" ) );
    $query->execute(["tag" => $tag ]);

    if ( $query->rowCount() ){
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
function get_posts_by_user( $user_id = 0, $auto_format = true ){
    
    if( !$user_id ){
        return [];
    }

    global $db;

    $query = $db->prepare( create_posts_query( "WHERE p.user_id = :uid" ) );
    $query->execute(["uid" => $user_id ]);

    if ( $query->rowCount() ){
        
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
        SELECT p.*, u.email, GROUP_CONCAT(t.tag SEPARATOR '-||-') AS tags
        FROM posts p
        LEFT JOIN posts_tags pt ON (p.id = pt.post_id)
        LEFT JOIN tags t ON  (t.id = pt.tag_id)
        LEFT JOIN phpauth_users u ON (p.user_id = u.id)
    ";
/*  zde musí zůstat, vytváříme funkci */
    if ( $where ){
        $query .= $where;

    }
    $query .= " GROUP BY p.id";
    // chtěli bychom přidat uspořádání článků - poslední vytvořen nahoře
    $query .= " ORDER BY p.created_at DESC";
    
    return trim( $query );
}

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

    // celean it up
    $post["title"] = plain( $post["title"] );
    $post["text"]  = plain( $post["text"] );
    $post["tags"]  = $post["tags"] ? explode("-||-", $post["tags"] ) : [];
    $post["tags"]  = array_map("plain", $post["tags"]);

    // tag me up
    if ( $post["tags"] ) foreach ( $post["tags"] as $tag ){
        $post["tag_links"][$tag] = BASE_URL . "/tag/" . urlencode($tag);
        $post["tag_links"][$tag] = filter_var( $post["tag_links"][$tag], FILTER_SANITIZE_URL );
    }

    // create link to post [ /post/:id/:slug ]
    $post["link"] = get_post_link( $post );
    // lets go on some dates
    $post["timestamp"] = strtotime( $post["created_at"] );
    $post["time"] = str_replace(" ", "&nbsp;", date("j M Y, G:i", $post["timestamp"]));
    $post["date"] = date("Y-m-d", $post["timestamp"]);
    // word limit
    $post["teaser"] = word_limiter($post["text"], 40);

    // format text
    if ( $format_text ){
        $post["text"] = filter_url( $post["text"] );
        $post["text"] = add_paragraphs( $post["text"] );
    }
    
    // user
    $post["email"] = filter_var( $post["email"], FILTER_SANITIZE_EMAIL );
    $post["user_link"] = BASE_URL . "/user/" . $post["user_id"];
    $post["user_link"] = filter_var($post["user_link"], FILTER_SANITIZE_URL);

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
    return $link;
    
    // $post["link"] = BASE_URL . "/post/{$post['id']}/{$post['slug']}";
    // $post['link'] = filter_var( $post['link'], FILTER_SANITIZE_URL );

}

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

    return $results;

}
/**
 * Valitade Post
 * 
 * Sanitize and validate, sister
 * 
 * @return array|bool
 */
function validate_post(){
    
    $title = filter_input( INPUT_POST, "title", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
    $text = filter_input( INPUT_POST, "text", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
    // FILTER_FLAG_NO_ENCODE_QUOTES - ignoruj uvozovky během sanitizace
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
        // v případě že formulář nevyplníme správně - smaže se nám obsah inputů
        // chceme přežít tento redirect a zároveň chceme uložit obsah inputů (abychom nemuseli vyplňovat znovu)
        // můžeme využít session, kde bude pole s hodnotami, které chceme aby se předvyplnili
        $_SESSION["form_data"] = [
            "title" => $title,
            "text" => $text,
            // pokud nějaké tagy máme, tak se uloží jako $tags - pokud ne, uloží se jako prázdné pole
            "tags" => $tags ?: [],
        ];

        return false;
    }

    // return values as array
    return compact('post_id', 'title', 'text', 'tags');
}