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

<div class="dashboard-content">
    <div class="history-title">
        <h2>History for <?= htmlspecialchars($monitorName !== '' ? $monitorName : $monitorUuid) ?></h2>
    </div>
    <p class="monitor-uuid">
        UUID:
        <span
            id="monitor-uuid-copy"
            title="Copy URL"
            style="cursor: pointer;"
            data-uuid="<?= htmlspecialchars($monitorUuid) ?>"
        >
            <?= htmlspecialchars($monitorUuid) ?>
        </span>
    </p>
    <p>Total pings: <?= $total ?></p>
    <hr/>
    <?php if (count($history) === 0) : ?>
        <p>No ping activity yet.</p>
    <?php else : ?>
        <ul class="history-list">
            <?php
            $count = count($history);
            for ($i = 0; $i < $count; $i++) :
                $entry = $history[$i];
                $next = $i + 1 < $count ? $history[$i + 1] : null;
                // Calculate interval to previous ping within this page (ignore last entry)
                $interval = null;
                if ($next !== null) {
                    try {
                        $t1 = new \DateTimeImmutable($entry->getPingedAt());
                        $t2 = new \DateTimeImmutable($next->getPingedAt());
                        $diffSec = max(0, $t1->getTimestamp() - $t2->getTimestamp());
                        $h = intdiv($diffSec, 3600);
                        $m = intdiv($diffSec % 3600, 60);
                        $s = $diffSec % 60;
                        $interval = sprintf('%02d:%02d:%02d', $h, $m, $s);
                    } catch (\Throwable $e) {
                        $interval = null;
                    }
                }
            ?>
                <li class="history-item">
                    <span class="history-time"><?= htmlspecialchars($entry->getPingedAt()) ?></span>
                    <?php if ($entry->getDurationMs() !== null) : ?>
                        <span class="history-duration">(<?= $entry->getDurationMs() ?> ms)</span>
                    <?php endif; ?>
                    <?php if ($interval !== null) : ?>
                        <span class="history-interval">+ <?= htmlspecialchars($interval) ?></span>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
        </ul>
    <?php endif; ?>

    <?php
        $totalPages = max(1, (int)ceil($total / $pageSize));
        $prevPage = max(1, $page - 1);
        $nextPage = min($totalPages, $page + 1);
    ?>
    <div class="pagination" style="display: flex; align-items: center; gap: 0.75rem;">
        <a
            class="page-button<?= $page <= 1 ? ' disabled' : '' ?>"
            href="/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $prevPage ?>"
            aria-label="Previous page"
        >&lt;</a>
        <span>Page <?= $page ?> / <?= $totalPages ?></span>
        <a
            class="page-button<?= $page >= $totalPages ? ' disabled' : '' ?>"
            href="/monitor/<?= htmlspecialchars($monitorUuid) ?>?page=<?= $nextPage ?>"
            aria-label="Next page"
        >&gt;</a>
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
