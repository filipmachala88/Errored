<?php 
    
    $page_title = 'Add new';
    include_once "_partials/header.php";
/*  nyní potřebujeme nový rout (abychom měli (jako uživatel) povolení na stránku)
    - jenže ten funguje tak, že máme /post/id (int) a my potřebujeme /post/new (string)
    - uděláme menší "hacK" v post.php (nechce se nám překopávat celý routing systém - proto radši používat package jako např. klein)
*/
?>

<section class="box">
    <form action="<?php echo BASE_URL ?>/_admin/add-item.php" method="post" class="post">
    <!-- upravíme na add-item.php -->

        <header class="post-header">
            <h1 class="box-heading">Add new post</h1>
        </header>

        <div class="form-group">
            <input type="text" name="title" class="form-control" value="" placeholder="title your shit">
        </div>

        <div class="form-group">
            <textarea class="form-control" name="text" rows="16" placeholder="write your shit"></textarea>
        </div>

        <div class="form-group">
            <?php foreach ( get_all_tags() as $tag ) : ?>
            <!-- nepotřebujeme getovat post id - zde budeme getovat poze všechny tagy (smažeme parametr funkce) -->
            
            <label class="checkbox">
                <input type="checkbox" name="tags[]" value="<?php echo $tag->id ?>"
                        <?php echo isset($tag->checked) && $tag->checked ? "checked" : "" ?>
                    >
                <?php echo plain($tag->tag) ?>
            </label>

            <?php endforeach ?>
        </div>
        <div class="form-group">
            <!-- jdeme přidávat nový post - nemusíme si přeposálat post id (hidden input smazat) --->
            <button type="submit" class="btn btn-primary">add post</button>
            <span class="or">
                <!-- link bude směřovat domů -->
                or <a href="<?php echo BASE_URL ?>">cancel</a>
            </span>
        </div>
    </form>
</section>

<?php include_once "_partials/footer.php"; ?>