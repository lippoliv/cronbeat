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
        <a href="/login/logout" class="logout-button">Logout</a>
    </div>
</div>

<?php if (isset($error) && $error !== null) : ?>
<div class='error'><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (isset($success) && $success !== null) : ?>
<div class='success'><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="dashboard-content">
    <div class="monitors-header">
        <h2>Your Monitors</h2>
        <form method="post" action="/dashboard/add" class="add-monitor-form">
            <input type="text" name="name" placeholder="Monitor name" required>
            <button type="submit" class="add-button">Add Monitor</button>
        </form>
    </div>

    <?php if (empty($monitors)) : ?>
    <p class="no-monitors">You don't have any monitors yet. Add one using the form above.</p>
    <?php else : ?>
    <div class="monitors-list">
        <?php foreach ($monitors as $monitor) : ?>
        <div class="monitor-item">
            <div class="monitor-info">
                <h3><?= htmlspecialchars($monitor['name']) ?></h3>
                <p class="monitor-uuid">UUID: <?= htmlspecialchars($monitor['uuid']) ?></p>
            </div>
            <a href="/dashboard/delete/<?= htmlspecialchars($monitor['uuid']) ?>" 
               class="delete-button" 
               onclick="return confirm('Are you sure you want to delete this monitor?')">
                Delete
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>