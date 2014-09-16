<?php

$error = false;

if (array_key_exists('username', $_POST)) {
    require_once "Classes/User.php";
    try {
        $user = new User($_POST['username']);
        if ($user->authenticate($_POST['password'])) {
            header("Location: ?a=user");
        } else {
            $error = true;
        }
    } catch (Exception $e) {
        $error = true;
    }
}

?>

<div>
    <?php if ($error) { ?>
        <span class="error">Wrong username or password.</span><br />
    <?php } ?>
    <form method="post" action="?a=login">
        User name: <input id="username" name="username" type="text" /><br />
        Password: <input id="password" name="password" type="password" /><br />
        <input type="submit" value="Log in" />
    </form>
</div>