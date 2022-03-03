<?php 
    // vychází z edit formuláře (stejný princip)

    try{
        // chci aby se naformátovalo - smažu false
        $post = get_post( segment(2) );
    }
    catch( PDOException $e ){
        $post = false;
    }

    if ( !$post ){
        flash()->error('doesnt exist');
        redirect('/');
    }

    $page_title = 'Delete / ' . $post->title;

    include_once("_partials/header.php");
?>

<h1>edit page</h1>

<section class="box">
    <form action="<?php echo BASE_URL ?>/_admin/delete-item.php" method="post" class="post">
        <header class="post-header">
            <h1 class="box-heading">
                Opravdu odstranit?
            </h1>
        </header>

        <blockquote class="form-group">
            <h3>&rdquo;<?php echo $post->title; ?>&rdquo;</h3>
            <p class="teaser"><?php echo $post->teaser; ?></p>
        </blockquote>

        <div class="form-group">
            <input name="post_id" value="<?php echo $post->id ?>" type="hidden">
            <button type="submit" class="btn btn-primary">Delete post</button>
            <span class="or">
                or <a href="<?php echo get_post_link( $post ) ?>">cancel</a>
            </span>
        </div>
    </form>
</section>

<?php include_once("_partials/footer.php"); ?>