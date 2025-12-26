<?php
/**
 * @var string|null $error
 * @var string|null $success
 * @var string $username
 * @var string $name
 * @var string $email
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

<?php if ($error !== null) : ?>
<div class='error'><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success !== null) : ?>
<div class='success'><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="profile-content">
    <div class="profile-section">
        <h2>General</h2>
        <form method="post" action="/profile">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
            </div>
            <button type="submit">Save</button>
        </form>
    </div>

    <div class="profile-section">
        <h2>Change Password</h2>
        <form method="post" action="/profile/password" class="hash-password">
            <div class="form-group">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Update Password</button>
        </form>
    </div>
</div>
