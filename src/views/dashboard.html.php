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
        <?php
            $tileClass = 'monitor-tile';
            $expected = $monitor->getExpectedIntervalMinutes();
            $grace = $monitor->getGracePeriodMinutes();
            $lastPing = $monitor->getLastPingAt();
            if ($expected !== null && $expected > 0 && $lastPing !== null && $lastPing !== '') {
                // try to parse as UTC timestamp
                $lastTs = null;
                try {
                    $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $lastPing, new \DateTimeZone('UTC'));
                    if ($dt !== false) {
                        $lastTs = $dt->getTimestamp();
                    }
                } catch (\Throwable $e) {
                    $lastTs = null;
                }
                if ($lastTs === null) {
                    $tmp = strtotime($lastPing);
                    if ($tmp !== false) { $lastTs = $tmp; }
                }
                if ($lastTs !== null) {
                    $elapsed = time() - $lastTs;
                    $expectedSec = $expected * 60;
                    $graceSec = ($grace ?? 0) * 60;
                    if ($elapsed > ($expectedSec + $graceSec)) {
                        $tileClass .= ' monitor-tile-alert';
                    } elseif ($elapsed > $expectedSec) {
                        $tileClass .= ' monitor-tile-warning';
                    }
                }
            }
        ?>
        <div class="<?= $tileClass ?>">
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
            <div class="monitor-actions"></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
