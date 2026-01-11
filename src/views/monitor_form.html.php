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

        <div class="form-group" style="display:flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
            <div style="flex:1 1 200px;">
                <label>Expected Interval (optional)</label>
                <div style="display:flex; gap:0.5rem;">
                    <div>
                        <input type="number" min="0" id="expected_interval_hours" name="expected_interval_hours" value="0" style="width:6rem;">
                        <div class="history-gap">hours</div>
                    </div>
                    <div>
                        <input type="number" min="0" max="59" id="expected_interval_minutes" name="expected_interval_minutes" value="0" style="width:6rem;">
                        <div class="history-gap">minutes</div>
                    </div>
                </div>
            </div>

            <div style="flex:1 1 200px;">
                <label>Grace Period (optional)</label>
                <div style="display:flex; gap:0.5rem;">
                    <div>
                        <input type="number" min="0" id="grace_period_hours" name="grace_period_hours" value="0" style="width:6rem;">
                        <div class="history-gap">hours</div>
                    </div>
                    <div>
                        <input type="number" min="0" max="59" id="grace_period_minutes" name="grace_period_minutes" value="0" style="width:6rem;">
                        <div class="history-gap">minutes</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="add-button">Add Monitor</button>
            <a href="/dashboard" class="cancel-button">Cancel</a>
        </div>
    </form>
</div>
