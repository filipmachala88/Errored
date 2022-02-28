<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? "$page_title / " : "" ?>this is a blog</title>
    <!-- booststrap stylesheet from: https://getbootstrap.com/docs/3.4/getting-started/ -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">

</head>
<body class="<?php echo segment(1) ? plain(segment(1)) : 'home' ?>">

<header class="container">
    <?php echo flash()->display() ?>
    <div class="navigation btn-group btn-group-xd">
        <a href="<?php echo BASE_URL ?>" class="btn btn-default">home</a>
    </div>
</header>

<main>
    <div class="container">
    