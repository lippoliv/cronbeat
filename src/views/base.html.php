<?php
/**
 * @var string $title Page title
 * @var string $content Page content
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="container">
        <?= $content ?>
    </div>

    <script src="/js/password-hash.js"></script>
</body>
</html>
