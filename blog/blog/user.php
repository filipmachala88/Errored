<?php
    $user = get_user( segment(2) );

    try{
        $results = get_posts_by_user( $user->uid );
        // logged id control
        // print_r($user->uid );
    }
    catch ( PDOException $e ){
        $results = [];
    }
    include_once("_partials/header.php");
    
?>
    <section class="box post-list">
        <h1 class="box-heading text-muted"><small>by</small><?php echo plain( $user->email ) ?></h1>
        <?php if ( count ( $results ) ) : foreach ( $results as $post ) : ?>
            <article id="post-<?php echo $post->$id ?>" class="post">
                <header class="post-header">
                    <h2>
                        <a href="<?php echo $post->link ?>">
                            <?php echo $post->title ?>
                        </a>
                        <time datetime="<?php echo $post->date ?>">
                            <small> /&nbsp;<?php echo $post->time ?></small>
                        </time>
                    </h2>
                    <?php if ( $post->tags ) : ?>
                        <p class="tags">
                            <?php foreach ($post->tag_links as $tag => $tag_link ) : ?>
                                <a href="<?php echo $tag_link ?>" class="btn btn-warning btn-xd"><small><?php echo $tag ?></small></a>
                            <?php endforeach ?>
                        </p>
                    <?php endif ?>
                </header>
                <div class="post-content">
                    <p>
                        <?php echo $post->teaser ?>
                    </p>
                </div>
                <div class="footer post-footer">
                    <a class="read-more" href="<?php echo $post->link ?>">
                        read more
                    </a>
                </div>
            </article>

        <?php endforeach; else : ?>
            <p> nejsou žádné příspěvky </p>
        <?php endif ?>
        
    </section>

<?php include_once("_partials/footer.php"); ?>