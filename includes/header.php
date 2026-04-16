<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$user = current_user();
$cartCount = count(cart_items());
$siteName = setting('site_name', 'Kom Dam Dokan');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $siteName) ?></title>
    <meta name="description" content="Kom Dam Dokan mobile fast ecommerce website.">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="topbar">
    <div class="container">Fast mobile-first grocery ecommerce for Bangladesh</div>
</div>
<header class="site-header">
    <div class="container header-row">
        <a href="<?= base_url('index.php') ?>" class="brand">Kom <span>Dam</span> Dokan</a>
        <form class="search-form" action="<?= base_url('search.php') ?>" method="get">
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search in Kom Dam Dokan" data-live-search>
            <button type="submit">Search</button>
            <div class="search-suggest" data-suggest-box></div>
        </form>
        <nav class="nav-links">
            <a href="<?= base_url('index.php') ?>">Home</a>
            <a href="<?= base_url('wishlist.php') ?>">Wishlist</a>
            <a href="<?= base_url('cart.php') ?>">Cart (<span data-cart-count><?= $cartCount ?></span>)</a>
            <a href="<?= base_url('blog.php') ?>">Blog</a>
            <?php if ($user): ?>
                <a href="<?= base_url('profile.php') ?>"><?= e($user['name']) ?></a>
                <?php if ((int) $user['is_admin'] === 1): ?><a href="<?= base_url('admin/index.php') ?>">Admin</a><?php endif; ?>
                <a href="<?= base_url('logout.php') ?>">Logout</a>
            <?php else: ?>
                <a href="<?= base_url('login.php') ?>">Login</a>
                <a href="<?= base_url('register.php') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php $flashes = get_flashes(); if ($flashes): ?>
        <div class="flash-wrap">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
