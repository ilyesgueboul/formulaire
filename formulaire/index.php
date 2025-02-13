<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'identification sécurisé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form action="process.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="logo">
                <img src="logo.png" alt="Logo">
            </div>

            <div class="form-group">
                <label for="username">Identifiant :</label>
                <input type="text" id="username" name="username" required pattern="[a-zA-Z0-9_]{3,20}" title="Entre 3 et 20 caractères alphanumériques ou '_'" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="off">
            </div>

            <div class="buttons">
                <button type="submit" name="action" value="login">Ok</button>
                <button type="reset">Reset</button>
                <button type="submit" formaction="add.php">Ajout compte</button>
            </div>
        </form>
    </div>
</body>
</html>
