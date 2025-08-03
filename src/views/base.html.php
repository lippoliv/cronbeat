<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'CronBeat' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="container">
        <?= isset($content) ? $content : '' ?>
    </div>

    <script src="/js/password-hash.js"></script>
</body>
</html>
