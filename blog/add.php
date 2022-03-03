<?php 
    
    $page_title = 'Add new';
    include_once "_partials/header.php";

    // nechám si předat data ze session
    if ( isset( $_SESSION["form_data"] ) )
    {
        // extracnem si - získám proměnné $title, $text a $tags
        extract( $_SESSION["form_data"] );
        // dále už data nepotřebuji - nechám je odstranit ze session
        unset( $_SESSION["form_data"] );
    }

?>

<section class="box">
    <form action="<?php echo BASE_URL ?>/_admin/add-item.php" method="post" class="post">
    <!-- upravíme na add-item.php -->

        <header class="post-header">
            <h1 class="box-heading">Add new post</h1>
        </header>

        <div class="form-group">
            <!--    nechám si zobrazit $title ze session a v textarea $text
                    - pokud nějaké data mám, zobrazí se, pokud nemám zobrazí se prázdný string
                    - ale v případě, že žádná data nemám - nemá se co zobrazit (nastane error)
                    - jedna varianta je kontrolovat, jestli je hodnota nastavená (isset), druhá je vypnout si v config "notice error"
            -->
            <input type="text" name="title" class="form-control" value="<?php if(isset($title)){echo $title;} ?>" placeholder="title your shit">
        </div>

        <div class="form-group">
            <textarea class="form-control" name="text" rows="16" placeholder="write your shit"><?php if(isset($text)){echo $text;} ?></textarea>
        </div>

        <div class="form-group">
            <?php foreach ( get_all_tags() as $tag ) : ?>
            
            <label class="checkbox">
                <input type="checkbox" name="tags[]" value="<?php echo $tag->id ?>"
                        <?php
                            // pokud vytváříme nový příspěvek, stále chybí zapamatování si tagů (v případě, že jsem nevyplnil nějaký z údajů)
                            // pokud má být checked nebo jsme ho vyklikali (existuje v $tags - pole, které se vrátilo z seasson) označ jako checked
                            echo isset($tag->checked) || (isset($tags) && in_array($tag->id, $tags ?: [] )) ? "checked" : ""
                        ?>
                    >
                <?php echo plain($tag->tag) ?>
            </label>

            <?php endforeach ?>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">add post</button>
            <span class="or">
                or <a href="<?php echo BASE_URL ?>">cancel</a>
            </span>
        </div>
    </form>
</section>

<?php include_once "_partials/footer.php"; ?>