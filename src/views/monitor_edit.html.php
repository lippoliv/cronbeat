<?php
/**
 * @var string $monitorUuid
 * @var string $name
 * @var string|null $error
 */
?>

<div class="monitor-form-container">
    <h2>Edit Monitor</h2>

    <?php if ($error !== null) : ?>
        <div class='error'><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/monitor/<?= htmlspecialchars($monitorUuid) ?>/edit" class="monitor-form">
        <div class="form-group">
            <label for="name">Monitor Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div class="form-actions" style="display:flex; gap: 0.5rem; align-items:center;">
            <button type="submit" class="add-button">Save</button>
            <a href="/monitor/<?= htmlspecialchars($monitorUuid) ?>" class="cancel-button">Cancel</a>
            <span style="flex:1 1 auto"></span>
            <a href="/dashboard/delete/<?= htmlspecialchars($monitorUuid) ?>"
               class="delete-button"
               onclick="return confirm('Are you sure you want to delete this monitor?')">
                Delete
            </a>
        </div>
    </form>
</div>
