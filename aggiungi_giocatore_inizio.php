<!-- aggiungi_giocatore_inizio.php - Pagina per aggiungere un giocatore in Rosa Inizio Stagione (Accessibile a tutti gli utenti) -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = htmlspecialchars($_POST['nome']);
    $ruolo = htmlspecialchars($_POST['ruolo']);
    $riferimento = htmlspecialchars($_POST['riferimento']);
    $stipendio = floatval($_POST['stipendio']);
    $scadenza = $_POST['scadenza'];
    $proprietario = htmlspecialchars($_POST['proprietario']); // Questo campo deve corrispondere all'utente loggato se non admin
    $squadra = htmlspecialchars($_POST['squadra']);

    // Se non è admin, imposta il proprietario all'utente loggato
    if (!$is_admin) {
        $proprietario = $username_logged_in;
    }

    $sql = "INSERT INTO giocatori (NOME, RUOLO, RIFERIMENTO, STIPENDIO, SCADENZA, PROPRIETARIO, SQUADRA) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddss", $nome, $ruolo, $riferimento, $stipendio, $scadenza, $proprietario, $squadra);

    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        $message = "Giocatore aggiunto con successo!";

        // Logga l'azione SOLO SE l'utente è un admin
        if ($is_admin) {
            $descrizione = "Giocatore '{$nome}' (Ruolo: {$ruolo}, Proprietario: {$proprietario}, Squadra: {$squadra}) aggiunto tramite 'Rosa Inizio Stagione'.";
            $log_sql = "INSERT INTO storico_modifiche (ID_GIOCATORE, AZIONE, DESCRIZIONE_MODIFICA, UTENTE_ADMIN) VALUES (?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $azione = 'AGGIUNTA';
            $log_stmt->bind_param("isss", $last_id, $azione, $descrizione, $username_logged_in);
            $log_stmt->execute();
            $log_stmt->close();
        }
        header("Location: rosa_inizio_stagione.php"); // Reindirizza alla pagina Rosa di Inizio Stagione
        exit;
    } else {
        $message = "Errore nell'aggiunta del giocatore: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Giocatore - Inizio Stagione</title>
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
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Aggiungi Giocatore (Rosa Inizio Stagione)</h2>
        <?php if ($message): ?>
            <p class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 <?php echo strpos($message, 'Errore') !== false ? 'bg-red-100 border-red-400 text-red-700' : ''; ?>" role="alert">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        <form action="aggiungi_giocatore_inizio.php" method="post">
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
                <input type="text" id="proprietario" name="proprietario" <?php echo !$is_admin ? 'readonly' : ''; ?> value="<?php echo !$is_admin ? htmlspecialchars($username_logged_in) : ''; ?>"
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php echo !$is_admin ? 'bg-gray-100 cursor-not-allowed' : ''; ?>">
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
            <a href="rosa_inizio_stagione.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200">Torna alla Rosa Inizio Stagione</a>
        </div>
    </div>
</body>
</html>