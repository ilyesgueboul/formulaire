<?php
session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(displayMessage("Erreur de connexion à la base de données.", "error"));
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo displayMessage("Veuillez remplir tous les champs.", "error");
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    echo displayMessage("Identifiant invalide.", "error");
    exit;
}

$attemptsFile = 'login_attempts.json';
$maxAttempts = 5;
$lockTime = 300;

$attemptsData = file_exists($attemptsFile) ? json_decode(file_get_contents($attemptsFile), true) : [];
$ip = $_SERVER['REMOTE_ADDR'];
$currentTime = time();

foreach ($attemptsData as $ipAddr => $attempt) {
    if ($attempt['last_attempt'] + $lockTime < $currentTime) {
        unset($attemptsData[$ipAddr]);
    }
}

if (isset($attemptsData[$ip]) && $attemptsData[$ip]['attempts'] >= $maxAttempts) {
    echo displayMessage("Trop de tentatives échouées. Réessayez plus tard.", "error");
    exit;
}

$stmt = $db->prepare('SELECT password FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    unset($attemptsData[$ip]);

    session_regenerate_id(true);
    $_SESSION['user'] = $username;

    file_put_contents("security.log", date("Y-m-d H:i:s") . " - Connexion réussie : $username - IP: $ip\n", FILE_APPEND);

    echo displayMessage("Vous êtes connecté !", "success");
} else {
    if (!isset($attemptsData[$ip])) {
        $attemptsData[$ip] = ['attempts' => 1, 'last_attempt' => $currentTime];
    } else {
        $attemptsData[$ip]['attempts']++;
        $attemptsData[$ip]['last_attempt'] = $currentTime;
    }

    file_put_contents("security.log", date("Y-m-d H:i:s") . " - Tentative échouée : $username - IP: $ip\n", FILE_APPEND);

    echo displayMessage("Identifiant ou mot de passe incorrect.", "error");
}

file_put_contents($attemptsFile, json_encode($attemptsData));

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
