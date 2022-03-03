<?php 
    // post edit id control
    // echo "<pre>";
    // print_r( "id:" . segment(2) );
    // echo "</pre>";

    try{
        $post = get_post( segment(2), false );
    }
    catch( PDOException $e ){
        $post = false;
    }

    if ( !$post ){
        flash()->error('doesnt exist');
        redirect('/');
    }

    $page_title = 'Edit / ' . $post->title;

    include_once("_partials/header.php");
?>

<h1>edit page</h1>

<section class="box">
    <form action="<?php echo BASE_URL ?>/_admin/edit-item.php" method="post" class="post">
        <header class="post-header">
            <h1 class="box-heading">
                Edit &ldquo;<?php echo plain( $post->title ) ?>&rdquo;
            </h1>
        </header>

        <div class="form-group">
            <input type="text" name="title" class="form-control" value="<?php echo $post->title ?>" placeholder="title your shit">
        </div>

        <div class="form-group">
            <textarea class="form-control" name="text" rows="16" placeholder="write your shit"><?php echo $post->text ?></textarea>
        </div>

        <div class="form-group">
            <?php foreach ( get_all_tags( $post->id ) as $tag ) : ?>
            <label class="checkbox">
                <input type="checkbox" name="tags[]" value="<?php echo $tag->id ?>"
                        <?php echo isset($tag->checked) && $tag->checked ? "checked" : "" ?>
                    >
                <?php echo plain($tag->tag) ?>
            </label>

            <?php endforeach ?>
        </div>
        <div class="form-group">
            <input name="post_id" value="<?php echo $post->id ?>" type="hidden">
            <button type="submit" class="btn btn-primary">Edit post</button>
            <span class="or">
                or <a href="<?php echo get_post_link( $post ) ?>">cancel</a>
            </span>
        </div>
    </form>
</section>

<?php include_once("_partials/footer.php"); ?>