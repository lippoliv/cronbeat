<?php
/**
 * @var string $title Page title
 * @var string $content Page content
 * @var string|null $containerClass Container CSS class
 * @var string|null $appVersion Application version
 * @var bool $showHeader Whether to show the header
 * @var string|null $username Username of the logged-in user
 * @var bool $isDashboard Whether the current page is the dashboard
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        // Normalize the view title so it doesn't repeat the app name
        $rawTitle = $title;
        $sanitizedTitle = trim(
            preg_replace(
                [
                    '/^\s*CronBeat\s*-\s*/i',   // strip leading "CronBeat - "
                    '/\s*-\s*CronBeat\s*$/i',   // strip trailing " - CronBeat"
                ],
                '',
                $rawTitle
            ) ?? ''
        );
        // Fallback if title becomes empty
        if ($sanitizedTitle === '') {
            $sanitizedTitle = $isDashboard ? 'Dashboard' : 'Page';
        }
        $documentTitle = htmlspecialchars($sanitizedTitle, ENT_QUOTES) . ' - CronBeat';
        ?>
    <title><?= $documentTitle ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="<?= $containerClass ?? 'container' ?>">
        <?php if ($showHeader) : ?>
            <div class="header">
                <div>
                    <h1>
                        <?php if ($isDashboard) : ?>
                            CronBeat - <?= htmlspecialchars($sanitizedTitle, ENT_QUOTES) ?>
                        <?php else : ?>
                            <a href="/dashboard">CronBeat</a> - <?= htmlspecialchars($sanitizedTitle, ENT_QUOTES) ?>
                        <?php endif; ?>
                    </h1>
                    <?php if ($appVersion !== null) : ?>
                        <div class="app-version"><?= htmlspecialchars($appVersion) ?></div>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <p>Welcome, <?= htmlspecialchars($username ?? '') ?>!</p>
                    <a href="/profile">profile</a>
                    <a href="/login/logout">logout</a>
                </div>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>

    <script src="/js/password-hash.js"></script>
</body>
</html>
