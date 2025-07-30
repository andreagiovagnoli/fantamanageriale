<!-- aggiungi_giocatore.php - Pagina per aggiungere un nuovo giocatore (Admin Only) -->
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

// Controlla se il modulo è stato inviato via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupera i dati dal modulo e li sanifica (htmlspecialchars per prevenire XSS)
    $nome = htmlspecialchars($_POST['nome']);
    $ruolo = htmlspecialchars($_POST['ruolo']); // Corretto: htmlspecialchars applicato correttamente
    $riferimento = htmlspecialchars($_POST['riferimento']);
    $stipendio = floatval($_POST['stipendio']); // Converte in float
    $scadenza = $_POST['scadenza']; // La data non richiede htmlspecialchars se usata in bind_param
    $proprietario = htmlspecialchars($_POST['proprietario']);
    $squadra = htmlspecialchars($_POST['squadra']);

    // Prepara la query SQL per inserire un nuovo giocatore
    $sql = "INSERT INTO giocatori (NOME, RUOLO, RIFERIMENTO, STIPENDIO, SCADENZA, PROPRIETARIO, SQUADRA) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // 'sssddss' specifica i tipi dei parametri: string, string, string, double, date, string, string
    $stmt->bind_param("sssddss", $nome, $ruolo, $riferimento, $stipendio, $scadenza, $proprietario, $squadra);

    // Esegui la query
    if ($stmt->execute()) {
        $last_id = $conn->insert_id; // Ottieni l'ID del giocatore appena inserito
        $message = "Giocatore aggiunto con successo!";

        // Logga l'azione se l'utente è un admin
        if ($is_admin) {
            $descrizione = "Giocatore '{$nome}' (Ruolo: {$ruolo}, Proprietario: {$proprietario}, Squadra: {$squadra}) aggiunto.";
            $log_sql = "INSERT INTO storico_modifiche (ID_GIOCATORE, AZIONE, DESCRIZIONE_MODIFICA, UTENTE_ADMIN) VALUES (?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $azione = 'AGGIUNTA';
            $log_stmt->bind_param("isss", $last_id, $azione, $descrizione, $username_logged_in);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: rose.php"); // Reindirizza alla pagina rose dopo l'aggiunta
        exit;
    } else {
        $message = "Errore nell'aggiunta del giocatore: " . $conn->error;
    }
    $stmt->close(); // Chiudi lo statement
}
$conn->close(); // Chiudi la connessione al database
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Giocatore</title>
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
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Aggiungi Nuovo Giocatore</h2>
        <?php if ($message): ?>
            <p class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 <?php echo strpos($message, 'Errore') !== false ? 'bg-red-100 border-red-400 text-red-700' : ''; ?>" role="alert">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        <form action="aggiungi_giocatore.php" method="post">
            <div class="mb-4">
                <label for="nome" class="block text-gray-700 text-sm font-semibold mb-2">Nome:</label>
                <input type="text" id="nome" name="nome" required
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="ruolo" class="block text-gray-700 text-sm font-semibold mb-2">Ruolo:</label>
                <input type="text" id="ruolo" name="ruolo"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="riferimento" class="block text-gray-700 text-sm font-semibold mb-2">Riferimento:</label>
                <input type="text" id="riferimento" name="riferimento"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="stipendio" class="block text-gray-700 text-sm font-semibold mb-2">Stipendio:</label>
                <input type="number" id="stipendio" name="stipendio" step="0.01"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="scadenza" class="block text-gray-700 text-sm font-semibold mb-2">Scadenza Contratto:</label>
                <input type="date" id="scadenza" name="scadenza"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="proprietario" class="block text-gray-700 text-sm font-semibold mb-2">Proprietario:</label>
                <input type="text" id="proprietario" name="proprietario"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-6">
                <label for="squadra" class="block text-gray-700 text-sm font-semibold mb-2">Squadra:</label>
                <input type="text" id="squadra" name="squadra"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                Aggiungi Giocatore
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="rose.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200">Torna alle Rose</a>
        </div>
    </div>
</body>
</html>