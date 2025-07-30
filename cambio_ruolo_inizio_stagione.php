<!-- cambio_ruolo_inizio_stagione.php - Nuova pagina per il Cambio Ruolo (Admin Only) -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

// Se non è admin, reindirizza o mostra un messaggio di accesso negato
if (!$is_admin) {
    echo "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'><title>Accesso Negato</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'><style>body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }</style></head><body class='flex items-center justify-center min-h-screen'><div class='bg-white p-8 rounded-lg shadow-md text-center'><h2 class='text-3xl font-bold text-red-700 mb-4'>Accesso Negato</h2><p class='text-gray-700 mb-6'>Non hai i permessi per accedere a questa pagina.</p><a href='dashboard.php' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200'>Torna alla Dashboard</a></div></body></html>";
    exit;
}

// Qui andrà la logica per la gestione del cambio ruolo di inizio stagione
// Potresti listare tutti i giocatori e permettere all'admin di modificare il loro ruolo.

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio Ruolo Inizio Stagione</title>
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
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Cambio Ruolo Inizio Stagione</h2>
        <p class="text-gray-700 mb-6">Questa sezione permette all'admin di gestire e applicare i cambi di ruolo dei giocatori all'inizio della stagione, in base alle liste ufficiali.</p>
        <p class="text-gray-600 mt-4">Qui potrai implementare la logica per aggiornare massivamente i ruoli dei giocatori.</p>
        <a href="dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 mt-6">Torna alla Dashboard</a>
    </div>
</body>
</html>