<!-- elimina_giocatore.php - Pagina per eliminare un giocatore -->
<?php
session_start(); // Inizia la sessione
// Controlla se l'utente è loggato, altrimenti reindirizza alla pagina di login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php'; // Include il file di connessione al database

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

// Se non è admin, reindirizza o mostra un messaggio di accesso negato
if (!$is_admin) {
    echo "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'><title>Accesso Negato</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'><style>body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }</style></head><body class='flex items-center justify-center min-h-screen'><div class='bg-white p-8 rounded-lg shadow-md text-center'><h2 class='text-3xl font-bold text-red-700 mb-4'>Accesso Negato</h2><p class='text-gray-700 mb-6'>Non hai i permessi per accedere a questa pagina.</p><a href='dashboard.php' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200'>Torna alla Dashboard</a></div></body></html>";
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Assicurati che l'ID sia un intero

    // Recupera i dati del giocatore PRIMA dell'eliminazione per il log
    $player_to_delete = null;
    if ($is_admin) {
        $sql_select_player = "SELECT NOME, PROPRIETARIO FROM giocatori WHERE ID = ?";
        $stmt_select_player = $conn->prepare($sql_select_player);
        $stmt_select_player->bind_param("i", $id);
        $stmt_select_player->execute();
        $result_select_player = $stmt_select_player->get_result();
        if ($result_select_player->num_rows == 1) {
            $player_to_delete = $result_select_player->fetch_assoc();
        }
        $stmt_select_player->close();
    }

    // Prepara la query SQL per eliminare il giocatore
    $sql = "DELETE FROM giocatori WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' per intero

    // Esegui la query
    if ($stmt->execute()) {
        // Logga l'azione se l'utente è un admin
        if ($is_admin && $player_to_delete) {
            $descrizione = "Giocatore '{$player_to_delete['NOME']}' (ID: {$id}, Proprietario: {$player_to_delete['PROPRIETARIO']}) eliminato.";
            $log_sql = "INSERT INTO storico_modifiche (ID_GIOCATORE, AZIONE, DESCRIZIONE_MODIFICA, UTENTE_ADMIN) VALUES (?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $azione = 'ELIMINAZIONE';
            $log_stmt->bind_param("isss", $id, $azione, $descrizione, $username_logged_in);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: rose.php"); // Reindirizza alla pagina rose dopo l'eliminazione
        exit;
    } else {
        echo "<p style='color: red; text-align: center; margin-top: 20px;'>Errore nell'eliminazione del giocatore: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red; text-align: center; margin-top: 20px;'>ID giocatore non specificato per l'eliminazione.</p>";
}
$conn->close(); // Chiudi la connessione al database
?>

<!-- bilanci.php - Pagina dedicata ai Bilanci -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';
// Qui potrai aggiungere la logica specifica per la sezione Bilanci
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilanci</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Bilanci</h2>
        <p class="text-gray-700 mb-6">Questa pagina mostrerà i bilanci e le statistiche finanziarie della tua squadra. Qui potrai visualizzare grafici, entrate, uscite e proiezioni.</p>
        <a href="dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">Torna alla Dashboard</a>
    </div>
</body>
</html>