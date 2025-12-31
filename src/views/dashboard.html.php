<?php
/**
 * @var string|null $error Error message to display
 * @var string|null $success Success message to display
 * @var array $monitors Array of MonitorData
 */
?>

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
        <div class="monitor-tile">
            <a class="monitor-link" href="/monitor/<?= htmlspecialchars($monitor->getUuid()) ?>">
                <div class="monitor-info">
                    <h3><?= htmlspecialchars($monitor->getName()) ?></h3>
                    <p class="monitor-uuid">UUID: <?= htmlspecialchars($monitor->getUuid()) ?></p>
                    <p class="monitor-last-ping">
                        Last ping:
                        <?php if ($monitor->getLastPingAt() !== null && $monitor->getLastPingAt() !== '') : ?>
                            <span class="last-ping-time">
                                <?= htmlspecialchars((string) $monitor->getLastPingAt()) ?>
                            </span>
                            <?php $lastDurationMs = $monitor->getLastDurationMs(); if ($lastDurationMs !== null) : ?>
                                <span class="last-ping-duration">
                                    (
                                    <?= htmlspecialchars(
                                        \Cronbeat\AppHelper::formatDuration($lastDurationMs)
                                    ) ?>
                                    )
                                </span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="last-ping-time">—</span>
                        <?php endif; ?>
                        <?php if ($monitor->hasPendingStart()) : ?>
                            <span class="spinner" title="waiting for ping"></span>
                        <?php endif; ?>
                    </p>
                </div>
            </a>
            <div class="monitor-actions">
                <a href="/dashboard/delete/<?= htmlspecialchars($monitor->getUuid()) ?>"
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
