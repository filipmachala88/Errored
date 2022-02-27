<?php
    $tag = urldecode( segment(2) );
/*  v druhéhém segmentu se skrává můj tag */
    $tag = plain($tag);
/*  rozdekóduji si tag a očistím */
    try{
        $results = get_posts_by_tag( $tag );
/*  chci vytáhnout příspěvky ale podle tagu - nová funkce, budu muset vytvořit
    - funkce je podobná get_post() funkci - můžu si ji zkopírovat (v functions-post.php)
*/
    }
    catch ( PDOException $e ){
        $results = [];
    }
    include_once("_partials/header.php");
    
?>
    <section class="box post-list">
        <h1 class="box-heading text-muted">Příspěvky tagu &ldquo;<?php echo $tag ?>&rdquo;</h1>
<!--    nechci mít vypsaný "home page" ale v uvozovkách vypsaný daný tag
        DATABÁZE: chci si přidat index (alter index s index type: "index" ) do tabuly "tags"
            - pokud bych měl hodně tagů - rychleji se v indexu bude hledat na základě podmínky (bude stačit prohledávat index, který si databáze vytvoří - je menší)
            - pointa je, že když vyhledáváme přes sloupec - měl by mít nastavený index v databázové tabulce
-->
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