<?php
    // post post id control
    // echo "<pre>";
    // print_r( "id:" . segment(2) );
    // echo "</pre>";

    // SQL INJECTION control
    // $id = segment(2);
    // $id = urldecode( $id );
/*  můžeme dělat i úpravy na kódu (např. % se přefitrují na mezery) */

    // echo "<pre>";
    // print_r( $id );
    // echo "</pre>";

    // databse dangerous code
    // $query = $db->query("
    //    SELECT * FROM posts
    //    WHERE id = $id
    // ");
    
    // database safe code
    // $query = $db->prepare("
    //    SELECT * FROM posts
    //    WHERE id = ?
    // ");
    // $query->execute([ $id ]);
/*  více způsobů jak zapsat */
    //  $query = $db->prepare("
    //    SELECT * FROM posts
    //    WHERE id = :first OR title LIKE :second
    // ");
/*  1. způsob - můžu zapsat jako pole */
    // $query->execute([
    //    "first"  => 2,
    //   "second" => "%Prince%",
    // ]);
/*  2. způsob (delší) - můžu si parametry nabindovat */
    // $id = 1;
    // $title = "%prince%";

    // $query->bindParam( ":first", $id, PDO::PARAM_INT );
    // $query->bindParam( ":second", $title, PDO::PARAM_STR );
/*  parametrů může být více - boolean atd.
    - můžu se cítit bezpečněji - definuji, jaký typ parametru musí být
    - PDO hodnoty zkontroluje a odfiltruje co je nebezpečné (nemusí tipovat, co bude za hodnoty - přímo mu deifnuji)
    - vždy pracujte přes Prepared statements
*/
    // $query->execute();

    // WHERE id = 1 OR 1 = 1 --
    // echo "<pre>";
    // print_r( $query->fetchAll( PDO::FETCH_ASSOC ) );
    // echo "</pre>";

    $id = segment(2);
    // add new post form
    if ( $id === "new" ){
        include_once "add.php";
        die();
    }
/*  pokud se v id nachází string new, tak místo příspěvku zobraz add.php */
    try{
        $post = get_post();
/*      pokusíme se provést get_post() funkci */
    }
    catch( PDOException $e ){
        $post = false;
/*      pokud se nám nepodaří, tak natavíme $post na false
        - to samé můžeme provést i na home stránce
*/
    }

    if ( !$post ){
        flash()->error("doesnt exist");
        redirect("/");
/*      pokud post neexistuje, vyhodíme flash message a redirectneme se zpět na homepage
        - musíme flash message někde vypsat -> v header.php
        - pozor! flash message nebude fungovat ve starších verzích PHP (5.4 a níže)
            - pokud máme starší server a háže errory - bude to kvůli tomuto
*/
    }

    $page_title = $post->title;
/*  chci mít pro každý příspěvek vlastní title
    - chci aby se do HTML title poslal title mé podstránky
    - nyní zkontrluji v <title>

*/

    include_once("_partials/header.php");
?>

<h1>single post</h1>
<section class="box">
    <article class="post">
        
        <header class="post-header">
            <h1 class="box-heading">
                <a href="<?php echo $post->link ?>"><?php echo $post->title ?></a>
                
                <a href="<?php echo get_edit_link( $post ) ?>" class="btn btn-xd edit-link">
                    <!--    vyskládá link na editaci postu
                            - můžeme si nazvat funkci jako get_edit_link s parametrem $post a paramer "edit/delete/post/" zpraovávat v samotné funkci - pro lepší přehled názvů
                            - říká se tomu tzv. Pomocné funkce
                    -->
                    edit
                </a>

                <time datetime="<?php echo $post->date ?>">
                    <small><?php echo $post->time ?></small>
                </time>
            </h1>
        </header>

        <div class="post-content">
            <?php echo $post->text ?>
        </div>
        <?php 
        
        // POST data controll
        // echo "<pre>";
        // print_r($post);
        // echo "</pre>";

        ?>

        <footer class="post-footer">
<!--    chtěl bych v příspěvku vypisovat tagy - můžu si zkopírovat kód z home.php
        - jenže ten stenjný kód se nám opakuje (duplikuje) - bude lepší z toho vytvořit parial (_partials)
        - dalo by se použít u vícero kódu na stránce - je to na nás (často jsou stránky kompromis mezi přehledností a rozdělením na části [partials])
            - např. ve fuunctions.php všechny ty quries co vytváříme jsou skoro stejné (kromě jednoho řádku na vytáhnutí dat (WHERE)
            - místo toho můžeme vytvořit jako funkci
-->
            <?php include "_partials/tags.php"; ?>
        </footer>
    </article>
</section>

<?php include_once("_partials/footer.php"); ?>