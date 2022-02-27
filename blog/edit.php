<?php 
    // post edit id control
    // echo "<pre>";
    // print_r( "id:" . segment(2) );
    // echo "</pre>";

    try{
        $post = get_post( segment(2), false );
/*      budu se snažit získat data o příspěvku ze segmentu 2
        - přidám false - chci čisté data z postu, tak jak byly vložené do databáze (tak jak je user napsal)
        - budeme chtít vytvořit edit formulář, kde se předvyplní hodnoty tak jak jich user zadal (žádné <a> elementy, žádné linky ani odstavce - nenaformátované)
*/
    }
    catch( PDOException $e ){
        $post = false;
    }

    if ( !$post ){
        flash()->error('doesnt exist');
        redirect('/');
    }

    $page_title = 'Edit / ' . $post->title;
/*  zmeníme title stránky na "Edit / this is a title" styl - vytvoříme specifický title pro edit page
    - nebue fungovat, vyhodí error, že se snažíme zavolat $post->title když pracuji s polem a ne objektem
    - důvod je, že v "functions-post.php" ve funkci format_post() měním na objekt, když ho naformátuje a vrátí (jako objekt)
    - jenže teď jsme zavolal get_post funkci bez autoformatu, takže podmínka if ($auto_format) neplatí
    - tedy na formátování (vrácení jako objekt) se nikdy nedostane - výsledek ses vrátí jako pole (fetch_assoc)
    - potřeba přidat stav pokud nechceme formátovat - v tom přípdaě $result castnu na objekt - nyní bude fungovat
    Nyní si vytvoříme samotný formulář na stránce edit
*/

/*  budeme se snažit chtít udělat to samé jak u post zobrazit příspěvek a nejprve kontrolovat, jestli existuje
    - můžeme si zkopírovat z post.php
*/

    include_once("_partials/header.php");
?>

<h1>edit page</h1>

<section class="box">
    <form action="<?php echo BASE_URL ?>/_admin/edit-item.php" method="post" class="post">
<!--    vytvoříme si formulář, který se bude odesílat na edit-item.php
        - jenže nyní adresujeme do složky _inc a v .htacces souboru jsme si zakázali přístup do _inc složky
        - proto by bylo dobré si udělat novou složky např. _admin do které si dáme edit-item.php, delete-item.php a add-item.php
-->
        <header class="post-header">
            <h1 class="box-heading">
                Edit &ldquo;<?php echo plain( $post->title ) ?>&rdquo;
                <!-- nadpis bude který post edituji -->
            </h1>
        </header>

        <div class="form-group">
            <input type="text" name="title" class="form-control" value="<?php echo $post->title ?>" placeholder="title your shit">
        </div>
        <!-- pak budeme mít input, pro změnu title (názvu), přidáme name="title" a  do textaread name="text" -->

        <div class="form-group">
            <textarea class="form-control" name="text" rows="16" placeholder="write your shit"><?php echo $post->text ?></textarea>
        </div>

        <div class="form-group">
            <?php foreach ( get_all_tags( $post->id ) as $tag ) : ?>
        <!--    proběhnu vše co se vytáhne z get_all_tags, pro každý z nich vytvořím nový checkbox
                - důležité je, aby name atribut měl složené závorky (name="tags[]") - chci když si vyberu 3 tagy, aby se odeslali všechny 3 tagy jako pole
                - odesílat se bude id (value="id") a číst se bude samotný text
                nyní bych však chtěl mít předvyplněné ty, které se týkají daného textu (při editaci)
                - můžu si rozšířit get_all_tags funkci - přidám tam volitelný post id parametr
                    - pokud zadám post id do funkce (která získává tagy), tak do výsledku výpisu všch tagů se přidá i boolean
                    - jestli tag patří příspěvku s id, který jsem tam poslal
        -->
            
            <label class="checkbox">
                <input type="checkbox" name="tags[]" value="<?php echo $tag->id ?>"
                        <?php echo isset($tag->checked) && $tag->checked ? "checked" : "" ?>
                    >
                <!--     pokud nějaký tag-checked atribut v poli a zároveň je true, tak vypiš "checked" do inputu, pokud ne vypiš prázdný string
                        - pozor na spárvné ukončení HTML tagu input - kód musí být uvnitř
                -->
                <?php echo plain($tag->tag) ?>
            </label>

            <?php endforeach ?>
        </div>
        <div class="form-group">
            <!--    budu tu chtít mít tlačítko na odeslání a link, který půjde zpět na stránku příspěvku
                    - jelikož chci link na stránku příspěvku, znovu jsem v situaci, kdy bych potřeboval skládat link (BASE URL / id / slug)
                    - měli bychom si udělat funkci na vyskládávání linku get_post_link() ve "functions-post.php"
            -->
            <input name="post_id" value="<?php echo $post->id ?>" type="hidden">
            <!-- name si nazveme post_id a hodnota (value) nastavena na post id -->
            <button type="submit" class="btn btn-primary">Edit post</button>
            <span class="or">
                or <a href="<?php echo get_post_link( $post ) ?>">cancel</a>
            </span>
        </div>
    </form>
</section>

<?php include_once("_partials/footer.php"); ?>