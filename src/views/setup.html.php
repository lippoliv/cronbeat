<h1>CronBeat Setup</h1>
<?php if ($error): ?>
<div class='error'><?= $error ?></div>
<?php endif; ?>
<form method="post" action="/setup" class="hash-password">
    <input type="hidden" name="action" value="setup">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Create Account</button>
</form>