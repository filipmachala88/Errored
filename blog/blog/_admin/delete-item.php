<?
// vychází z edit-item.php

// include
require '../_inc/config.php';

    // nepotřebujeme nic validovat (user nic nezadává) - smazat
    // potřebuuji kontrolovat post_id - zkopíruji si z validate_post() funkce z functions-post.php
    // skrz formuulář dostávám post_id a musíme zkontrolovat:
    // 1. zda je inteager
    $post_id = filter_input( INPUT_POST, "post_id", FILTER_VALIDATE_INT );
    // 2. zda takový post máme
    // does this even exist?
    if ( !$post_id || !$post = get_post( $post_id, false ) ){
        flash()->error("no such post");
        redirect("back");
    }
    // is this the autor
    if ( !can_edit($post)){
        flash()->error("what are you trying to pull here");
        redirect("back");
    }
    // vytvoření delete query
    $query = $db->prepare("
        DELETE FROM posts
        WHERE id = :post_id
    ");
    // executneme - vrátí false/true
    $delete = $query->execute([
        "post_id" => $post_id
    ]);
    // pokud se nepodaří (false)
    if ( ! $delete ){
        flash()->warning("sorry, girl");
        redirect("backk");
    }
    // pokud se podaří (true) - musíme vymazat i tagy (z jiné tabulky)
    $query = $db->prepare("
            DELETE FROM posts_tags
            WHERE post_id = :post_id
    ");

    $query->execute([
        "post_id" => $post_id
    ]);

    flash()->success("goodbye, swet post");
    redirect("/");
   