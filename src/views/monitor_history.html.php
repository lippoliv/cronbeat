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
    <p class="monitor-uuid">UUID: <span id="monitor-uuid-copy" title="Copy URL" style="cursor: pointer;" data-uuid="<?= htmlspecialchars($monitorUuid) ?>"><?= htmlspecialchars($monitorUuid) ?></span></p>
    <p>Total pings: <?= $total ?></p>
    <hr/>
    <?php if (count($history) === 0): ?>
        <p>No ping activity yet.</p>
    <?php else: ?>
        <ul class="history-list">
            <?php foreach ($history as $entry): ?>
                <li class="history-item">
                    <span class="history-time"><?= htmlspecialchars($entry->getPingedAt()) ?></span>
                    <?php if ($entry->getDurationMs() !== null): ?>
                        <span class="history-duration">(<?= $entry->getDurationMs() ?> ms)</span>
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
    <div class="pagination" style="display: flex; align-items: center; gap: 0.75rem;">
        <button type="button"
                style="width: auto;"
                class="page-btn"
                onclick="window.location.href='/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $prevPage ?>'"
                <?= $page <= 1 ? 'disabled' : '' ?>>
            &lt;
        </button>
        <span>Page <?= $page ?> / <?= $totalPages ?></span>
        <button type="button"
                style="width: auto;"
                class="page-btn"
                onclick="window.location.href='/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $nextPage ?>'"
                <?= $page >= $totalPages ? 'disabled' : '' ?>>
            &gt;
        </button>
    </div>

    <script>
        (function () {
            var el = document.getElementById('monitor-uuid-copy');
            if (!el) { return; }
            el.addEventListener('click', function () {
                var uuid = el.getAttribute('data-uuid') || '';
                var url = window.location.origin + '/api/ping/' + uuid;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).catch(function () {});
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = url;
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    document.body.removeChild(ta);
                }
            });
        })();
    </script>

</div>
