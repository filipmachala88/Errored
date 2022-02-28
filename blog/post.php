<?php
    // post post id control
    // echo "<pre>";
    // print_r( "id:" . segment(2) );
    // echo "</pre>";

    // SQL INJECTION control
    // $id = segment(2);
    // $id = urldecode( $id );

    // echo "<pre>";
    // print_r( $id );
    // echo "</pre>";

    // databse dangerous code
    // $query = $db->query("
    //    SELECT * FROM posts
    //    WHERE id = $id
    // ");
    
    // database safe code
    // $query = $db->prepare("
    //    SELECT * FROM posts
    //    WHERE id = ?
    // ");
    // $query->execute([ $id ]);

    //  $query = $db->prepare("
    //    SELECT * FROM posts
    //    WHERE id = :first OR title LIKE :second
    // ");
    
    // $query->execute([
    //    "first"  => 2,
    //   "second" => "%Prince%",
    // ]);

    // $id = 1;
    // $title = "%prince%";

    // $query->bindParam( ":first", $id, PDO::PARAM_INT );
    // $query->bindParam( ":second", $title, PDO::PARAM_STR );

    // $query->execute();

    // WHERE id = 1 OR 1 = 1 --
    // echo "<pre>";
    // print_r( $query->fetchAll( PDO::FETCH_ASSOC ) );
    // echo "</pre>";

    $id = segment(2);
    // add new post form
    if ( $id === "new" ){
        include_once "add.php";
        die();
    }
    try{
        $post = get_post();
    }
    catch( PDOException $e ){
        $post = false;
    }

    if ( !$post ){
        flash()->error("doesnt exist");
        redirect("/");
    }

    $page_title = $post->title;

    include_once("_partials/header.php");
?>

<h1>single post</h1>
<section class="box">
    <article class="post">
        
        <header class="post-header">
            <h1 class="box-heading">
                <a href="<?php echo $post->link ?>"><?php echo $post->title ?></a>
                
                <a href="<?php echo get_edit_link( $post ) ?>" class="btn btn-xd edit-link">
                    edit
                </a>

                <time datetime="<?php echo $post->date ?>">
                    <small><?php echo $post->time ?></small>
                </time>
            </h1>
        </header>

        <div class="post-content">
            <?php echo $post->text ?>
        </div>
        <?php 
        
        // POST data controll
        // echo "<pre>";
        // print_r($post);
        // echo "</pre>";

        ?>

        <footer class="post-footer">
            <?php include "_partials/tags.php"; ?>
        </footer>
    </article>
</section>

<?php include_once("_partials/footer.php"); ?>