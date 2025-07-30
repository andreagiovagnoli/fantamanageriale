<!-- rosa_inizio_stagione.php - Nuova pagina per la Rosa di Inizio Stagione -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

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
    $stmt->bind_param("s", $username_logged_in);
    $stmt->execute();
    $result = $stmt->get_result();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosa di Inizio Stagione</title>
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
            background-color: #2563eb; /* blue-700 */
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
            <h2 class="text-3xl font-bold text-gray-800">Rosa di Inizio Stagione (1 Luglio)</h2>
            <div>
                <a href="aggiungi_giocatore_inizio.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200 mr-2">
                    Aggiungi Giocatore
                </a>
                <a href="rose.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                    Torna a Rose
                </a>
            </div>
        </div>

        <p class="text-gray-700 mb-6">
            <?php
            if ($is_admin) {
                echo "Come **Admin**, puoi visualizzare e gestire tutti i giocatori. Hai permessi completi (aggiungi, modifica, elimina).";
            } else {
                echo "Stai visualizzando i giocatori che hanno **" . htmlspecialchars($username_logged_in) . "** come proprietario. Puoi aggiungere ed eliminare giocatori, ma non modificarli.";
            }
            ?>
        </p>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider rounded-tl-lg">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ruolo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Riferimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Stipendio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Scadenza</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Proprietario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Squadra</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider rounded-tr-lg">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['ID']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['NOME']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['RUOLO']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['RIFERIMENTO']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['STIPENDIO'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['SCADENZA']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['PROPRIETARIO']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['SQUADRA']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($is_admin): ?>
                                <a href="modifica_giocatore.php?id=<?php echo $row['ID']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Modifica</a>
                                <?php endif; ?>
                                <a href="elimina_giocatore_inizio.php?id=<?php echo $row['ID']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo giocatore? L\'azione verrà registrata se sei un admin.');" class="text-red-600 hover:text-red-900">Elimina</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mt-4">Nessun giocatore trovato per questa rosa.</p>
        <?php endif; ?>

    </div>
    <?php
    if ($stmt) {
        $stmt->close(); // Chiudi lo statement se è stato usato
    }
    $conn->close(); // Chiudi la connessione al database
    ?>
</body>
</html>