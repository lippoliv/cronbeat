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
        <a href="/profile">profile</a>
        <a href="/login/logout">logout</a>
    </div>
</div>

<?php if ($error !== null) : ?>
<div class='error'><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success !== null) : ?>
<div class='success'><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="dashboard-content">
    <div class="monitors-header">
        <h2>Your Monitors</h2>
        <a href="/dashboard/new-monitor" class="add-monitor-button">Add New Monitor</a>
    </div>

    <?php if (count($monitors) === 0) : ?>
    <p class="no-monitors">You don't have any monitors yet. Add one using the button above.</p>
    <?php else : ?>
    <div class="monitors-grid">
        <?php foreach ($monitors as $monitor) : ?>
        <a class="monitor-tile" href="/dashboard/monitor/<?= htmlspecialchars($monitor['uuid']) ?>">
            <div class="monitor-info">
                <h3><?= htmlspecialchars($monitor['name']) ?></h3>
                <p class="monitor-uuid">UUID: <?= htmlspecialchars($monitor['uuid']) ?></p>
                <p class="monitor-last-ping">
                    Last ping:
                    <?php if (isset($monitor['last_ping_at']) && $monitor['last_ping_at'] !== ''): ?>
                        <span class="last-ping-time"><?= htmlspecialchars((string) $monitor['last_ping_at']) ?></span>
                        <?php if (isset($monitor['last_duration_ms']) && is_numeric($monitor['last_duration_ms'])): ?>
                            <span class="last-ping-duration">(<?= (int) $monitor['last_duration_ms'] ?> ms)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="last-ping-time">—</span>
                    <?php endif; ?>
                    <?php if (isset($monitor['pending_start']) && (int) $monitor['pending_start'] === 1): ?>
                        <span class="spinner" title="waiting for ping"></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="monitor-actions">
                <a href="/dashboard/delete/<?= htmlspecialchars($monitor['uuid']) ?>"
                   class="delete-button"
                   onclick="return confirm('Are you sure you want to delete this monitor?')"
                   onclick="event.stopPropagation();">
                    Delete
                </a>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
