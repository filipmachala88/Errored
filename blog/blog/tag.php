<?php
    $tag = urldecode( segment(2) );
    $tag = plain($tag);
    try{
        $results = get_posts_by_tag( $tag );
    }
    catch ( PDOException $e ){
        $results = [];
    }
    include_once("_partials/header.php");
    
?>
    <section class="box post-list">
        <h1 class="box-heading text-muted">Příspěvky tagu &ldquo;<?php echo $tag ?>&rdquo;</h1>
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