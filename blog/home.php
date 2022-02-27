<?php 
    try{
        $results = get_posts();
/*      do proměnné si dám get_posts - zatím nemáme, jdeme si vytvořit (function-post) */
    }
    catch ( PDOException $e ){
        // also handle errors maybe
        $results = [];
/*      pokud chceme, tak si sem můžeme dopsat vyspořádání s chybami (zapsat do souboru atd.)
        - try catch nemusíte používat, ale už víte, že máte tu schopnost
*/
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
<!--        count - spočítá kolik položek máme v $results poli
            - pokud bude více jak 0 - můžeme provést foreach, kde is každý prvek uložíme jako $post - abychom mohli s daty pracovat
            NYNÍ MÁME format_post() FUNKCI
            - můžeme zavolat kdekoliv - i na stránce samotného příspěvku
            - můžu chtít, aby get_posts() - výpis položek z DB do pole, funkce formátovala data sama pomocí funkce format_posts() - nemusím ji mít zde
                - v get_posts() v "functions-post" tedy zavoláme pro každou položku v poli
--> 
            <article id="post-<?php echo $post->$id ?>" class="post">
                <header class="post-header">
                    <h2>
                        <a href="<?php echo $post->link ?>">
                    <!--    chci mít link ve formátu: post/35/this-is-a-slug
                            - abych tam měl i slug (kvůli SEO)
                            1. můžu použít base url constantu pro adresu webu
                            2. můžu použít id z datbáze
                            3. můžu použít slug z databáze

                    -->
                            <?php echo $post->title ?>
                    <!--    jelikož vypisuji do HTML, nesmím zapomenout si to profiltrovat (abych se vyhl cross site scripting) -->
                        </a>
                        <time datetime="<?php echo $post->date ?>">
                            <small> /&nbsp;<?php echo $post->time ?></small>
                        <!--        nyní máme čas, ale neodpovídá správnému vizuálnímu formátu (nyní 2022-6-12)
                                    - víme, že máme time() funkci, která dokáže formátovat čas, jenže z unixového (timestamp) formátu (vypadá 12646548)
                                    - můžeme převést pomocí funkce strtotime(), která převede na timestamp
                                    - následě použijeme samotnou time() funkci, v prvním atgumentě si určím formát datumu
                                    + můžem i čas filtrovat
                                    víme, že HTML5 <time> atribut umí evidovat čas pro čtečky a roboty
                                        - akorát s trochu jiným formátem
                        -->
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
                    <!--    do obsahu si dáme text
                            - pokud nevím proč se sem dává plain funkce - v todoapp aplikaci je to napsané
                            toto není stránka příspěvku - nechci sem celý text, ale spíš malý "teaser"
                            - vytvoříme si v functions-string funkci tzv. word_limiter()
                            - pak vložíme a jako parametr napíšeme do kolika písmen chceme limitovat
                    -->
                    </p>
                </div>
                <div class="footer post-footer">
                    <a class="read-more" href="<?php echo $post->link ?>">
                    <!--    1. odhalili jsme situace, že se mi opakují linky postů - chce to funkci
                            2. na to že je to HTML kód je tu až moc formátování, plain funkcí a vytváření datumů (až moc PHP)
                                - bylo by lepší kdyby se to provedle někde jinde, zde jen vypíšeme
                                - vytvoříme si nato funkci format_post() ve functions-post
                    -->
                        read more
                    </a>
                </div>
            </article>

        <?php endforeach; else : ?>
        <!-- v opačném případě (podmínka neplatila) se vypíše text -->
            <p> nejsou žádné příspěvky </p>
        <?php endif ?>
        
    </section>

<?php include_once("_partials/footer.php"); ?>