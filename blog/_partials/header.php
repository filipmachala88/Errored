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

    <?php if ( logged_in() ) : $logged_in = get_user() ?>
    <div class="navigation">
        <div class="btn-group btn-group-sm pull-left">
            <a href="<?php echo BASE_URL ?>" class="btn btn-default">all posts</a>
            <a href="<?php echo BASE_URL ?>/user/<?php echo $logged_in->uid ?>" class="btn btn-default">my posts</a>
            <!-- link na vytvoření příspěvku -->
            <a href="<?php echo BASE_URL ?>/post/new" class="btn btn-default">add new</a>
        </div>
        <div class="btn-group btn-group-sm pull-right">
            <span class="username small"><?php print_r($logged_in->email) ?></span>
            <a href="<?php echo BASE_URL ?>/logout" class="btn-default logout">logout</a>
        </div>
    </div>
    <?php endif ?>
</header>

<main>
    <div class="container">
    