<?php
/**
 * @var string $monitorUuid
 * @var string $monitorName
 * @var array<int, array{entry: \Cronbeat\PingData, gap: ?string}> $historyWithGaps
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
    <?php if (count($historyWithGaps) === 0) : ?>
        <p>No ping activity yet.</p>
    <?php else : ?>
        <ul class="history-list">
            <?php foreach ($historyWithGaps as $item) : ?>
                <?php
                    $entry = $item['entry'];
                    $gap = $item['gap'];
                ?>
                <li class="history-item">
                    <span class="history-time"><?= htmlspecialchars($entry->getPingedAt()) ?></span>
                    <?php $durationMs = $entry->getDurationMs(); if ($durationMs !== null) : ?>
                        <span class="history-duration">(<?= htmlspecialchars(\Cronbeat\AppHelper::formatDuration($durationMs)) ?>)</span>
                    <?php endif; ?>
                    <?php if ($gap !== null) : ?>
                        <span class="history-gap">+ <?= htmlspecialchars($gap) ?></span>
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
