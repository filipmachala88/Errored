<?php if ( $post->tags ) : ?>
<!--  ne každý příspěvek musí mít tagy - musíme kontrolovat, zda nějaké má -->
    <p class="tags">
        <?php foreach ($post->tag_links as $tag => $tag_link ) : ?>
        <!--    pokud ano necháme je všechny vypsat
                - jeho text je $tag
                - jeho link je $tag_link
                nyní po kliknutí na tag vyhodí 404 - musíme přiat novou routu pro tagy (v index.php)
                - musím si vytvořit i tag.php soubor - můžu si zkopírovat home.php stránku (bude to podobné)
        -->
            <a href="<?php echo $tag_link ?>" class="btn btn-warning btn-xd"><small><?php echo $tag ?></small></a>
        <?php endforeach ?>
    </p>
<?php endif ?>