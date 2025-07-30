<!-- modifica_giocatore.php - Pagina per modificare un giocatore esistente -->
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

$message = ''; // Variabile per messaggi di successo o errore
$player = null; // Variabile per memorizzare i dati del giocatore da modificare

// Se è stato passato un ID tramite GET (per visualizzare il modulo di modifica)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Assicurati che l'ID sia un intero
    $sql = "SELECT * FROM giocatori WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' per intero
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $player = $result->fetch_assoc(); // Recupera i dati del giocatore
    } else {
        $message = "Giocatore non trovato.";
    }
    $stmt->close();
}

// Se il modulo è stato inviato via POST (per salvare le modifiche)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Recupera i dati originali del giocatore PRIMA della modifica per il log
    $old_player_data = null;
    $sql_old = "SELECT * FROM giocatori WHERE ID = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    if ($result_old->num_rows == 1) {
        $old_player_data = $result_old->fetch_assoc();
    }
    $stmt_old->close();

    // Recupera i dati aggiornati dal modulo e li sanifica
    $nome = htmlspecialchars($_POST['nome']);
    $ruolo = htmlspecialchars($_POST['ruolo']);
    $riferimento = htmlspecialchars($_POST['riferimento']);
    $stipendio = floatval($_POST['stipendio']);
    $scadenza = $_POST['scadenza'];
    $proprietario = htmlspecialchars($_POST['proprietario']);
    $squadra = htmlspecialchars($_($_POST['squadra']));

    // Prepara la query SQL per aggiornare il giocatore
    $sql = "UPDATE giocatori SET NOME = ?, RUOLO = ?, RIFERIMENTO = ?, STIPENDIO = ?, SCADENZA = ?, PROPRIETARIO = ?, SQUADRA = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    // 'sssddssi' specifica i tipi dei parametri
    $stmt->bind_param("sssddssi", $nome, $ruolo, $riferimento, $stipendio, $scadenza, $proprietario, $squadra, $id);

    // Esegui la query
    if ($stmt->execute()) {
        $message = "Giocatore aggiornato con successo!";

        // Logga l'azione se l'utente è un admin e ci sono state modifiche
        if ($is_admin && $old_player_data) {
            $changes = [];
            // Confronta i valori e registra solo i cambiamenti
            if ($old_player_data['NOME'] !== $nome) $changes[] = "Nome da '{$old_player_data['NOME']}' a '{$nome}'";
            if ($old_player_data['RUOLO'] !== $ruolo) $changes[] = "Ruolo da '{$old_player_data['RUOLO']}' a '{$ruolo}'";
            if ($old_player_data['RIFERIMENTO'] !== $riferimento) $changes[] = "Riferimento da '{$old_player_data['RIFERIMENTO']}' a '{$riferimento}'";
            if (floatval($old_player_data['STIPENDIO']) !== $stipendio) $changes[] = "Stipendio da '{$old_player_data['STIPENDIO']}' a '{$stipendio}'";
            if ($old_player_data['SCADENZA'] !== $scadenza) $changes[] = "Scadenza da '{$old_player_data['SCADENZA']}' a '{$scadenza}'";
            if ($old_player_data['PROPRIETARIO'] !== $proprietario) $changes[] = "Proprietario da '{$old_player_data['PROPRIETARIO']}' a '{$proprietario}'";
            if ($old_player_data['SQUADRA'] !== $squadra) $changes[] = "Squadra da '{$old_player_data['SQUADRA']}' a '{$squadra}'";

            if (!empty($changes)) {
                $descrizione = "Giocatore '{$nome}' (ID: {$id}) modificato. Campi cambiati: " . implode(", ", $changes) . ".";
                $log_sql = "INSERT INTO storico_modifiche (ID_GIOCATORE, AZIONE, DESCRIZIONE_MODIFICA, UTENTE_ADMIN) VALUES (?, ?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $azione = 'MODIFICA';
                $log_stmt->bind_param("isss", $id, $azione, $descrizione, $username_logged_in);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }

        header("Location: rose.php"); // Reindirizza alla pagina rose dopo l'aggiornamento
        exit;
    } else {
        $message = "Errore nell'aggiornamento del giocatore: " . $conn->error;
    }
    $stmt->close();
}
$conn->close(); // Chiudi la connessione al database
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Giocatore</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container bg-white p-8 rounded-lg shadow-md w-full max-w-lg mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Modifica Giocatore</h2>
        <?php if ($message): ?>
            <p class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 <?php echo strpos($message, 'Errore') !== false ? 'bg-red-100 border-red-400 text-red-700' : ''; ?>" role="alert">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <?php if ($player): ?>
        <form action="modifica_giocatore.php" method="post">
            <input type="hidden" name="id" value="<?php echo $player['ID']; ?>">
            <div class="mb-4">
                <label for="nome" class="block text-gray-700 text-sm font-semibold mb-2">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($player['NOME']); ?>" required
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="ruolo" class="block text-gray-700 text-sm font-semibold mb-2">Ruolo:</label>
                <input type="text" id="ruolo" name="ruolo" value="<?php echo htmlspecialchars($player['RUOLO']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="riferimento" class="block text-gray-700 text-sm font-semibold mb-2">Riferimento:</label>
                <input type="text" id="riferimento" name="riferimento" value="<?php echo htmlspecialchars($player['RIFERIMENTO']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="stipendio" class="block text-gray-700 text-sm font-semibold mb-2">Stipendio:</label>
                <input type="number" id="stipendio" name="stipendio" step="0.01" value="<?php echo htmlspecialchars($player['STIPENDIO']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="scadenza" class="block text-gray-700 text-sm font-semibold mb-2">Scadenza Contratto:</label>
                <input type="date" id="scadenza" name="scadenza" value="<?php echo htmlspecialchars($player['SCADENZA']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="proprietario" class="block text-gray-700 text-sm font-semibold mb-2">Proprietario:</label>
                <input type="text" id="proprietario" name="proprietario" value="<?php echo htmlspecialchars($player['PROPRIETARIO']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-6">
                <label for="squadra" class="block text-gray-700 text-sm font-semibold mb-2">Squadra:</label>
                <input type="text" id="squadra" name="squadra" value="<?php echo htmlspecialchars($player['SQUADRA']); ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                Aggiorna Giocatore
            </button>
        </form>
        <?php else: ?>
            <a href="rose.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200">Torna alle Rose</a>
        <?php endif; ?>
    </div>
</body>
</html>