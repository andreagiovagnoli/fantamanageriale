<!-- bilancio_pre_asta_estiva.php - Nuova pagina per il Bilancio Pre Asta Estiva -->
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
$voci_pre_asta_estiva = [
    1 => "INTROITI DIRITTI TELEVISIVI ESTIVI (stagione precedente)",
    2 => "BILANCIO DI FINE STAGIONE STAGIONE PRECEDENTE",
    3 => "MERCATO PRE ASTA ESTIVA",
    4 => "SVINCOLI EXTRA SERIE A ESTIVI",
    5 => "TAGLI FORZATI PRE ASTA ESTIVA",
    6 => "SPONSOR TECNICO - BONUS ALLA FIRMA"
];

$user_to_view_or_edit = $username_logged_in; // Default to current logged-in user

// Se è admin, gestisci la selezione dell'utente tramite dropdown
if ($is_admin) {
    // Recupera tutti gli utenti (escluso admin) per il dropdown
    $all_users = [];
    $sql_all_users = "SELECT username FROM utenti WHERE username != 'admin' ORDER BY username ASC";
    $result_all_users = $conn->query($sql_all_users);
    while ($row_user = $result_all_users->fetch_assoc()) {
        $all_users[] = $row_user['username'];
    }

    // Se un utente è stato selezionato dal dropdown, visualizza i suoi dati
    if (isset($_GET['view_user']) && in_array($_GET['view_user'], $all_users)) {
        $user_to_view_or_edit = htmlspecialchars($_GET['view_user']);
    } elseif (!empty($all_users)) {
        // Altrimenti, per admin, visualizza i dati del primo utente non-admin per default
        $user_to_view_or_edit = $all_users[0];
    } else {
        $user_to_view_or_edit = null;
    }
}


// Recupera i valori esistenti per l'utente determinato ($user_to_view_or_edit)
$current_values = [];
if ($user_to_view_or_edit) { // Solo se c'è un utente da visualizzare
    $sql_fetch = "SELECT voce_id, valore FROM bilanci WHERE username = ? AND voce_id BETWEEN 1 AND 6";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("s", $user_to_view_or_edit);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    while ($row = $result_fetch->fetch_assoc()) {
        $current_values[$row['voce_id']] = $row['valore'];
    }
    $stmt_fetch->close();
}


// Gestione del POST: Solo utenti normali possono salvare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_admin) { // Solo se NON è admin e ha inviato il form
    foreach ($voci_pre_asta_estiva as $id => $nome_voce) {
        $valore = isset($_POST['voce_' . $id]) ? floatval(str_replace(',', '.', $_POST['voce_' . $id])) : 0; // Gestisce virgola decimale

        // Inserisci o aggiorna il valore nel database per l'utente loggato
        $sql_upsert = "INSERT INTO bilanci (username, voce_id, valore) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valore = ?";
        $stmt_upsert = $conn->prepare($sql_upsert);
        $stmt_upsert->bind_param("sidd", $username_logged_in, $id, $valore, $valore);
        $stmt_upsert->execute();
        $stmt_upsert->close();
    }
    $message = "Valori aggiornati con successo!";
    // Ricarica i valori dopo l'aggiornamento (per l'utente loggato)
    $current_values = [];
    $sql_fetch_after_save = "SELECT voce_id, valore FROM bilanci WHERE username = ? AND voce_id BETWEEN 1 AND 6";
    $stmt_fetch_after_save = $conn->prepare($sql_fetch_after_save); // Ri-usa la query di fetch iniziale per l'utente loggato
    $stmt_fetch_after_save->bind_param("s", $username_logged_in);
    $stmt_fetch_after_save->execute();
    $result_fetch_after_save = $stmt_fetch_after_save->get_result();
    while ($row = $result_fetch_after_save->fetch_assoc()) {
        $current_values[$row['voce_id']] = $row['valore'];
    }
    $stmt_fetch_after_save->close();

} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $message = "Solo gli utenti non-admin possono modificare i propri valori in questa sezione.";
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilancio Pre Asta Estiva</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">BILANCIO PRE ASTA ESTIVA</h2>
            <a href="bilanci_overview.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                Torna ai Bilanci
            </a>
        </div>

        <?php if ($message): ?>
            <p class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 <?php echo strpos($message, 'Errore') !== false ? 'bg-red-100 border-red-400 text-red-700' : ''; ?>" role="alert">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <div class="mb-6 bg-gray-100 p-4 rounded-lg border border-gray-200">
                <label for="user_select" class="block text-gray-700 text-sm font-semibold mb-2">Visualizza Bilancio Utente:</label>
                <select id="user_select" class="shadow-sm border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        onchange="if(this.value) window.location.href = 'bilancio_pre_asta_estiva.php?view_user=' + this.value;">
                    <option value="">Seleziona un utente</option>
                    <?php foreach ($all_users as $user_option): ?>
                        <option value="<?php echo htmlspecialchars($user_option); ?>" <?php echo ($user_option === $user_to_view_or_edit) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user_option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($user_to_view_or_edit): ?>
                    <p class="text-gray-600 mt-2">Stai visualizzando il bilancio di: <span class="font-bold"><?php echo htmlspecialchars($user_to_view_or_edit); ?></span></p>
                <?php else: ?>
                    <p class="text-gray-600 mt-2">Nessun utente selezionato o disponibile per la visualizzazione.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p class="text-gray-700 mb-6">Questa sezione dettaglia la situazione finanziaria prima dell'asta estiva, includendo:</p>

        <form method="POST" action="bilancio_pre_asta_estiva.php<?php echo $is_admin && $user_to_view_or_edit ? '?view_user=' . urlencode($user_to_view_or_edit) : ''; ?>">
            <ol class="list-decimal list-inside text-gray-800 space-y-4 pl-5">
                <?php foreach ($voci_pre_asta_estiva as $id => $nome_voce):
                    $input_classes = "shadow-sm appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full md:w-48";
                    $readonly_attr = '';
                    if ($is_admin) {
                        $input_classes .= " bg-gray-100 cursor-not-allowed";
                        $readonly_attr = 'readonly';
                    }
                ?>
                    <li class="flex items-center justify-between flex-wrap">
                        <label for="voce_<?php echo $id; ?>" class="font-semibold w-full md:w-auto md:flex-grow mr-4 mb-2 md:mb-0">
                            <?php echo htmlspecialchars($nome_voce); ?>:
                        </label>
                        <input type="number" id="voce_<?php echo $id; ?>" name="voce_<?php echo $id; ?>" step="0.01"
                               value="<?php echo htmlspecialchars($current_values[$id] ?? 0); ?>"
                               <?php echo $readonly_attr; ?>
                               class="<?php echo $input_classes; ?>">
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php if (!$is_admin): ?>
                <div class="mt-8 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md shadow-sm transition duration-200">
                        Salva Modifiche
                    </button>
                </div>
            <?php else: ?>
                <p class="text-gray-600 mt-8 text-center">Solo gli utenti non-admin possono modificare i valori in questa sezione. L'admin può solo visualizzare tramite il menù a tendina.</p>
            <?php endif; ?>
        </form>

    </div>
</body>
</html>