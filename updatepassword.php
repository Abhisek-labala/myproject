
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Reset Password</h1>

    <form method="post" action="process-reset-password.php">

        <label for="email" >Email </label>
        <input type="email" id="email" name="email">
        <label for="password">New password</label>
        <input type="password" id="password" name="upassword">

        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password" name="ucpassword">

        <button>Update Password</button>
    </form>

</body>
</html>