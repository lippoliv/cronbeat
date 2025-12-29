<?php
/**
 * @var string $title
 * @var string|null $error Error message to display
 * @var string|null $success Success message to display
 * @var int $currentVersion Current database version
 * @var int $expectedVersion Expected database version
 * @var bool $needsMigration Whether migration is needed
 */
?>
<h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>

<?php if (isset($error) && $error !== '') : ?>
<div class='error'><?= $error ?></div>
<?php endif; ?>

<?php if (isset($success) && $success !== '') : ?>
<div class='success'><?= $success ?></div>
<?php endif; ?>

<div class="migration-info">
    <p>Current database version: <strong><?= $currentVersion ?></strong></p>
    <p>Expected database version: <strong><?= $expectedVersion ?></strong></p>

    <?php if ($needsMigration) : ?>
        <div class="migration-needed">
            <p>Your database needs to be updated to the latest version.</p>
            <p>This will add new features and fix potential issues.</p>

            <form method="post" action="/migrate">
                <button type="submit" class="migrate-button">Update Database</button>
            </form>

            <p class="migration-note">You can also run the following command from the terminal:</p>
            <pre class="command">php src/cli.php migrate</pre>
        </div>
    <?php else : ?>
        <div class="migration-up-to-date">
            <p>Your database is up to date. No migration needed.</p>
        </div>
    <?php endif; ?>
</div>

<div class="navigation">
    <a href="/login" class="button">Back to Login</a>
</div>
