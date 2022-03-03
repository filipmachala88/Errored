<?php if ( $post->tags ) : ?>
    <p class="tags">
        <?php foreach ($post->tag_links as $tag => $tag_link ) : ?>
            <a href="<?php echo $tag_link ?>" class="btn btn-warning btn-xd"><small><?php echo $tag ?></small></a>
        <?php endforeach ?>
    </p>
<?php endif ?>