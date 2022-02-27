<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? "$page_title / " : "" ?>this is a blog</title>
<!--    existuje title? pokkud ano nechám ho vypsat pokud ne nevypíši nic -->

<!--    pokud budu vkládat na server je potřeba dát URL
            - můžeme rovnou použít naši BASE_URL konstantu
            - to stejné provést i pro footer.php
        jenže konstanta se pořád opakuje, proto místo toho můžeme udělat funkci
-->
    <!-- booststrap stylesheet from: https://getbootstrap.com/docs/3.4/getting-started/ -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">

</head>
<!--    je dobré mít class pro body (např. kdybychom chtěli mít pro každou podstránku jiný body vzhled)
        - pokud máme segment(1)
        - tak body class bude odfiltrovaný segment(1) v opačném případě "home" (pokud nemáme první segment jsme na home-page)       
-->
<body class="<?php echo segment(1) ? plain(segment(1)) : 'home' ?>">

<header class="container">
    <?php echo flash()->display() ?>
    <div class="navigation btn-group btn-group-xd">
        <a href="<?php echo BASE_URL ?>" class="btn btn-default">home</a>
    </div>
    <!-- chci tlačítko zpět (abych nemusel klikat na prohlžeč šipku) -->
</header>

<main>
    <div class="container">
    