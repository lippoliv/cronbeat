<?php
/**
 * @var string $monitorUuid
 * @var string $monitorName
 * @var array<\Cronbeat\PingData> $history
 * @var int $page
 * @var int $pageSize
 * @var int $total
 */
?>
<div class="dashboard-header">
    <h1><a href="/dashboard">CronBeat Dashboard</a></h1>
    <div class="user-info">
        <p>Welcome, <?= htmlspecialchars($username) ?>!</p>
        <a href="/profile">profile</a>
        <a href="/login/logout">logout</a>
    </div>
</div>

<div class="dashboard-content">
    <div class="history-title">
        <h2>History for <?= htmlspecialchars($monitorName !== '' ? $monitorName : $monitorUuid) ?></h2>
    </div>
    <p class="monitor-uuid">UUID: <?= htmlspecialchars($monitorUuid) ?></p>
    <p>Total entries: <?= $total ?></p>
    <hr/>
    <?php if (count($history) === 0): ?>
        <p>No ping activity yet.</p>
    <?php else: ?>
        <ul class="history-list">
            <?php foreach ($history as $entry): ?>
                <li class="history-item">
                    <span class="history-time"><?= htmlspecialchars($entry->getPingedAt()) ?></span>
                    <?php if ($entry->getDurationMs() !== null): ?>
                        <span class="history-duration"><?= $entry->getDurationMs() ?> ms</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
        $totalPages = max(1, (int)ceil($total / $pageSize));
        $prevPage = max(1, $page - 1);
        $nextPage = min($totalPages, $page + 1);
    ?>
    <div class="pagination">
        <a class="page-link<?= $page <= 1 ? ' disabled' : '' ?>" href="/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $prevPage ?>">Prev</a>
        <span>Page <?= $page ?> / <?= $totalPages ?></span>
        <a class="page-link<?= $page >= $totalPages ? ' disabled' : '' ?>" href="/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $nextPage ?>">Next</a>
    </div>

</div>
