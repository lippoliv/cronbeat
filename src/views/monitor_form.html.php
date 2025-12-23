<?php
/**
 * @var string|null $error Error message to display
 */
?>
<div class="monitor-form-container">
    <h2>Add New Monitor</h2>

    <?php if ($error !== null) : ?>
    <div class='error'><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/dashboard/add" class="monitor-form">
        <div class="form-group">
            <label for="name">Monitor Name</label>
            <input type="text" id="name" name="name" placeholder="Enter monitor name" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="add-button">Add Monitor</button>
            <a href="/dashboard" class="cancel-button">Cancel</a>
        </div>
    </form>
</div>
