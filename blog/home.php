<?php 
    try{
        $results = get_posts();
    }
    catch ( PDOException $e ){
        // also handle errors maybe
        $results = [];
    }
    // get_posts() function output control
    // echo "<pre>";
    // print_r( $results );
    // echo "</pre>";
    include_once("_partials/header.php");
    
?>
    <section class="box post-list">
        <h1 class="box-heading text-muted">home page</h1>
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
                    <?php

                        include "_partials/tags.php";
                        
                        // tag_links output control (tags)
                        // echo "<pre>";
                        // print_r( $post->tag_links );
                        // echo "</pre>";

                    ?>
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