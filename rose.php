<!-- rose.php - Pagina dedicata alle Rose -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin'); // Controlla se l'utente è l'admin

$sql = "";
$stmt = null;
$result = null;

if ($is_admin) {
    // Se è l'admin, seleziona tutti i giocatori
    $sql = "SELECT * FROM giocatori ORDER BY NOME";
    $result = $conn->query($sql);
} else {
    // Se è un utente normale, seleziona solo i giocatori con il suo nome come PROPRIETARIO
    $sql = "SELECT * FROM giocatori WHERE PROPRIETARIO = ? ORDER BY NOME";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username_logged_in); // 's' per stringa
    $stmt->execute();
    $result = $stmt->get_result();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rose della Squadra</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        table th, table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        table th {
            background-color: #4299e1;
            color: white;
            text-align: left;
            font-weight: 600;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .section-card-small {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-radius: 0.5rem;
            padding: 1.5rem; /* p-6 */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* shadow-sm */
            border: 1px solid;
        }
        .section-card-small:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1); /* shadow-md */
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Rose della Squadra</h2>
            <div>
                <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                    Torna alla Dashboard
                </a>
            </div>
        </div>

        <p class="text-gray-700 mb-6">
            Gestisci qui le tue rose e i dettagli dei giocatori.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="rosa_inizio_stagione.php" class="section-card-small bg-blue-50 border-blue-200">
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Rosa di Inizio Stagione (1 Luglio)</h3>
                <p class="text-gray-800 text-sm">Gestisci la rosa iniziale della stagione. Tutti possono aggiungere ed eliminare, solo l'admin può modificare.</p>
            </a>
            <!-- Potresti aggiungere altre sottosezioni qui, es. "Rosa Attuale", "Storico Rose", ecc. -->
        </div>

        <!-- La visualizzazione della tabella giocatori è stata spostata in rosa_inizio_stagione.php e/o in altre sottosezioni -->

    </div>
    <?php
    if ($stmt) {
        $stmt->close(); // Chiudi lo statement se è stato usato
    }
    $conn->close(); // Chiudi la connessione al database
    ?>
</body>
</html>