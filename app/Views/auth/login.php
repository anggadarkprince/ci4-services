<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
</head>
<body>
<h3>Login User</h3>
<form action="<?= site_url('login/login') ?>" method="post">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Username or email">
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Your password">
    </div>
    <button type="submit">Login Now</button>
</form>
</body>
</html>