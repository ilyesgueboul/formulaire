<?php
try {
    $message = "";

    if (!file_exists('database.sqlite')) {
        $db = new PDO('sqlite:database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )");

        chmod('database.sqlite', 0600);

        $message = "Base de données et table créées avec succès.";
    } else {
        $message = "La base de données existe déjà.";
    }

    echo displayMessage($message, "success");

} catch (PDOException $e) {
    error_log("[" . date("Y-m-d H:i:s") . "] Erreur SQL : " . $e->getMessage() . "\n", 3, "errors.log");
    echo displayMessage("Une erreur est survenue. Veuillez contacter l'administrateur.", "error");
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
