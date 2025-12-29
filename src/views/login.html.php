<?php
/**
 * @var string|null $error Error message to display
 */
?>
<h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
<?php if (isset($error) && $error !== '') : ?>
<div class='error'><?= $error ?></div>
<?php endif; ?>
<form method="post" action="index.php" class="hash-password">
    <input type="hidden" name="action" value="login">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>
