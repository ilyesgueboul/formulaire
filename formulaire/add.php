<?php
session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(displayMessage("CSRF token validation failed.", "error"));
}

try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        echo displayMessage("Veuillez remplir tous les champs.", "error");
        exit;
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        echo displayMessage("Le nom d'utilisateur doit contenir entre 3 et 20 caractères alphanumériques ou '_'.", "error");
        exit;
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $userExists = $stmt->fetchColumn();

    if ($userExists) {
        echo displayMessage("Ce nom d'utilisateur est déjà pris. Veuillez en choisir un autre.", "error");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    if ($stmt->execute([$username, $hashedPassword])) {
        echo displayMessage("Nouvel utilisateur ajouté avec succès.", "success");
    } else {
        echo displayMessage("Erreur lors de l'ajout de l'utilisateur.", "error");
    }

} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage(), 3, "errors.log");
    echo displayMessage("Une erreur est survenue. Veuillez réessayer plus tard.", "error");
}

function displayMessage($message, $type = "info") {
    $color = $type === "success" ? "#28a745" : ($type === "error" ? "#dc3545" : "#007bff");
    return "<div style='text-align: center; margin-top: 20px;'>
                <p style='font-size: 18px; color: $color; font-weight: bold;'>$message</p>
                <button onclick='window.location.href=\"index.php\"' 
                    style='background-color: #007bff; color: white; border: none; padding: 10px 15px; 
                    border-radius: 4px; cursor: pointer; font-size: 16px;'>
                    Retour
                </button>
            </div>";
}
?>
