<!-- storico_modifiche.php - Pagina dedicata allo storico delle modifiche (visibile solo all'admin) -->
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
    echo "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'><title>Accesso Negato</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'><style>body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }</style></head><body class='flex items-center justify-center min-h-screen'><div class='bg-white p-8 rounded-lg shadow-md text-center'><h2 class='text-3xl font-bold text-red-700 mb-4'>Accesso Negato</h2><p class='text-gray-700 mb-6'>Non hai i permessi per visualizzare questa pagina.</p><a href='dashboard.php' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200'>Torna alla Dashboard</a></div></body></html>";
    exit;
}

$sql = "SELECT * FROM storico_modifiche ORDER BY DATA_MODIFICA DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storico Modifiche</title>
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
            background-color: #ef4444; /* red-500 */
            color: white;
            text-align: left;
            font-weight: 600;
        }
        table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Storico Modifiche Giocatori</h2>
            <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                Torna alla Dashboard
            </a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider rounded-tl-lg">ID Modifica</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID Giocatore</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Azione</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Descrizione Modifica</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Data Modifica</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider rounded-tr-lg">Utente Admin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['ID']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['ID_GIOCATORE'] ?? 'N/A'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['AZIONE']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($row['DESCRIZIONE_MODIFICA']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['DATA_MODIFICA']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['UTENTE_ADMIN']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mt-4">Nessuna modifica registrata nello storico.</p>
        <?php endif; ?>

    </div>
    <?php $conn->close(); // Chiudi la connessione al database ?>
</body>
</html>