<?php
/**
 * @var string|null $error Error message to display
 * @var string|null $success Success message to display
 * @var string $username Username of the logged-in user
 * @var array $monitors Array of monitors, each with uuid and name
 */
?>
<div class="dashboard-header">
    <h1>CronBeat Dashboard</h1>
    <div class="user-info">
        <p>Welcome, <?= htmlspecialchars($username) ?>!</p>
        <a href="/login/logout">logout</a>
    </div>
</div>

<?php if (isset($error) && $error !== null) : ?>
<div class='error'><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (isset($success) && $success !== null) : ?>
<div class='success'><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="dashboard-content">
    <div class="monitors-header">
        <h2>Your Monitors</h2>
        <a href="/dashboard/new-monitor" class="add-monitor-button">Add New Monitor</a>
    </div>

    <?php if (empty($monitors)) : ?>
    <p class="no-monitors">You don't have any monitors yet. Add one using the button above.</p>
    <?php else : ?>
    <div class="monitors-grid">
        <?php foreach ($monitors as $monitor) : ?>
        <div class="monitor-tile">
            <div class="monitor-info">
                <h3><?= htmlspecialchars($monitor['name']) ?></h3>
                <p class="monitor-uuid">UUID: <?= htmlspecialchars($monitor['uuid']) ?></p>
            </div>
            <div class="monitor-actions">
                <a href="/dashboard/delete/<?= htmlspecialchars($monitor['uuid']) ?>" 
                   class="delete-button" 
                   onclick="return confirm('Are you sure you want to delete this monitor?')">
                    Delete
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>