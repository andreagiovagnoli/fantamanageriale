<!-- bilanci_overview.php - Nuova pagina di riepilogo per la sezione Bilanci -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

// Definizioni delle voci di bilancio come da immagine
$voci_bilancio = [
    1 => "INTROITI DIRITTI TELEVISIVI ESTIVI (stagione precedente)",
    2 => "BILANCIO DI FINE STAGIONE STAGIONE PRECEDENTE",
    3 => "MERCATO PRE ASTA ESTIVA",
    4 => "SVINCOLI EXTRA SERIE A ESTIVI",
    5 => "TAGLI FORZATI PRE ASTA ESTIVA",
    6 => "SPONSOR TECNICO - BONUS ALLA FIRMA",
    7 => "RIMANENZA ASTA ESTIVA",
    8 => "MERCATO POST ASTA ESTIVA",
    9 => "RINNOVI CONTRATTUALI ESTIVI",
    10 => "AMPLIAMENTO STADIO",
    11 => "INGAGGI PRIMA META' DI STAGIONE",
    12 => "SVINCOLI EXTRA SERIE A FRA L'ASTA ESTIVA E IL 31/12",
    13 => "MERCATO PRE ASTA INVERNALE",
    14 => "SVINCOLI EXTRA SERIE A DURANTE IL MERCATO INVERNALE",
    15 => "TAGLI FORZATI INVERNALI PRE ASTA INVERNALE",
    16 => "INTROITI DIRITTI TELEVISIVI INVERNALI",
    17 => "RINNOVI CONTRATTUALI INVERNALI",
    18 => "RIMANENZA ASTA INVERNALE",
    19 => "MERCATO POST ASTA INVERNALE",
    20 => "SVINCOLI EXTRA SERIE A POST ASTA INVERNALE",
    21 => "CONTROLLO BONUS",
    22 => "SPONSOR TECNICO",
    23 => "INTROITI COMPETIZIONI",
    24 => "INTROITI STADIO",
    25 => "MERCATO PRE CHIUSURA BILANCIO",
    26 => "INGAGGI FINALI"
];

$bilanci_utenti = []; // Array per memorizzare i dati di bilancio per tutti gli utenti o solo per l'utente loggato

// --- MODIFICA: Recupera gli utenti da visualizzare nelle colonne ---
$users_to_show_in_columns = [];
if ($is_admin) {
    // Admin vede tutti gli utenti tranne se stesso
    $sql_users = "SELECT username FROM utenti WHERE username != 'admin' ORDER BY username ASC";
    $result_users = $conn->query($sql_users);
    while ($row_user = $result_users->fetch_assoc()) {
        $users_to_show_in_columns[] = $row_user['username'];
    }
} else {
    // Utente normale vede solo se stesso
    $users_to_show_in_columns[] = $username_logged_in;
}

// Recupera i dati di bilancio per gli utenti selezionati
foreach ($users_to_show_in_columns as $user) {
    $bilanci_utenti[$user] = [];
    $sql_data = "SELECT voce_id, valore FROM bilanci WHERE username = ?";
    $stmt_data = $conn->prepare($sql_data);
    $stmt_data->bind_param("s", $user);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    while ($row_data = $result_data->fetch_assoc()) {
        $bilanci_utenti[$user][$row_data['voce_id']] = $row_data['valore'];
    }
    $stmt_data->close();
}

// Calcolo dei totali B1, B2, B3, B4
function calculate_bilancio_totals($user_data) {
    $b_totals = [
        'B1' => 0,
        'B2_raw' => 0, // Keep B2 raw total for B3 calculation
        'B3_raw' => 0, // Keep B3 raw total for B3 calculation
        'B4_raw' => 0, // Keep B4 raw total for B4 calculation
        'B2' => 0,
        'B3' => 0,
        'B4' => 0
    ];

    // B1: Somma dei punti da 1 a 6
    for ($i = 1; $i <= 6; $i++) {
        $b_totals['B1'] += ($user_data[$i] ?? 0);
    }

    // B2_raw: Somma dei punti da 7 a 12
    for ($i = 7; $i <= 12; $i++) {
        $b_totals['B2_raw'] += ($user_data[$i] ?? 0);
    }
    $b_totals['B2'] = $b_totals['B2_raw']; // B2 is just the sum of 7-12

    // B3: B2 + Somma dei punti da 13 a 17
    $b_totals['B3'] = $b_totals['B2_raw']; // Start B3 with the calculated B2_raw (sum of 7-12)
    for ($i = 13; $i <= 17; $i++) {
        $b_totals['B3'] += ($user_data[$i] ?? 0);
    }

    // B4: Somma dei punti da 18 a 26
    for ($i = 18; $i <= 26; $i++) {
        $b_totals['B4'] += ($user_data[$i] ?? 0);
    }

    return $b_totals;
}

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
        table th, table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            text-align: right; /* Default for numeric columns */
        }
        table th {
            background-color: #4299e1;
            color: white;
            text-align: left;
            font-weight: 600;
        }
        table td:first-child {
            text-align: left; /* Keep first column (Voce) left-aligned */
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Bilanci</h2>
            <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                Torna alla Dashboard
            </a>
        </div>

        <p class="text-gray-700 mb-6">Seleziona la fase del bilancio che desideri consultare o visualizza il riepilogo annuale.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
            <a href="bilancio_pre_asta_estiva.php" class="section-card-small bg-blue-100 border-blue-300">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">BILANCIO PRE ASTA ESTIVA</h3>
                <p class="text-gray-700 text-sm">Riepilogo finanziario prima dell'asta estiva.</p>
            </a>
            <a href="bilancio_inizio_mercato_invernale.php" class="section-card-small bg-green-100 border-green-300">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">BILANCIO INIZIO MERCATO INVERNALE</h3>
                <p class="text-gray-700 text-sm">Situazione finanziaria all'apertura del mercato invernale.</p>
            </a>
            <a href="bilancio_pre_asta_invernale.php" class="section-card-small bg-yellow-100 border-yellow-300">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">BILANCIO PRE ASTA INVERNALE</h3>
                <p class="text-gray-700 text-sm">Panoramica economica prima dell'asta di riparazione invernale.</p>
            </a>
            <a href="bilancio_finale_fine_stagione.php" class="section-card-small bg-red-100 border-red-300">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">BILANCIO FINALE FINE STAGIONE</h3>
                <p class="text-gray-700 text-sm">Consuntivo finale della stagione calcistica.</p>
            </a>
        </div>

        <h3 class="text-2xl font-bold text-gray-800 mb-4">Riepilogo Bilanci</h3>
        <?php if (!empty($users_to_show_in_columns)): ?>
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200 mb-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider rounded-tl-lg">Voce</th>
                            <?php foreach ($users_to_show_in_columns as $user): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider <?php echo $user === end($users_to_show_in_columns) ? 'rounded-tr-lg' : ''; ?>"><?php echo htmlspecialchars($user); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $voce_counter = 0;
                        foreach ($voci_bilancio as $voce_id => $voce_nome):
                            $voce_counter++;
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo "{$voce_id} - " . htmlspecialchars($voce_nome); ?></td>
                                <?php foreach ($users_to_show_in_columns as $user): ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($bilanci_utenti[$user][$voce_id] ?? 0, 2, ',', '.') . ' €'; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <?php if ($voce_id == 6): ?>
                                <tr class="font-bold bg-gray-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">B1 - BILANCIO PRE ASTA ESTIVA</td>
                                    <?php foreach ($users_to_show_in_columns as $user):
                                        $b_totals = calculate_bilancio_totals($bilanci_utenti[$user]); ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">
                                            <?php echo number_format($b_totals['B1'], 2, ',', '.') . ' €'; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endif; ?>

                            <?php if ($voce_id == 12): ?>
                                <tr class="font-bold bg-gray-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">B2 - BILANCIO INIZIO MERCATO INVERNALE</td>
                                    <?php foreach ($users_to_show_in_columns as $user):
                                        $b_totals = calculate_bilancio_totals($bilanci_utenti[$user]); ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">
                                            <?php echo number_format($b_totals['B2'], 2, ',', '.') . ' €'; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endif; ?>

                            <?php if ($voce_id == 17): ?>
                                <tr class="font-bold bg-gray-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">B3 - BILANCIO PRE ASTA INVERNALE</td>
                                    <?php foreach ($users_to_show_in_columns as $user):
                                        $b_totals = calculate_bilancio_totals($bilanci_utenti[$user]); ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">
                                            <?php echo number_format($b_totals['B3'], 2, ',', '.') . ' €'; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>
                        
                        <?php // B4: Always last ?>
                        <tr class="font-bold bg-gray-200">
                            <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">B4 - BILANCIO FINALE FINE STAGIONE</td>
                            <?php foreach ($users_to_show_in_columns as $user):
                                $b_totals = calculate_bilancio_totals($bilanci_utenti[$user]); ?>
                                <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900">
                                    <?php echo number_format($b_totals['B4'], 2, ',', '.') . ' €'; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mt-4">Nessun dato di bilancio disponibile.</p>
        <?php endif; ?>

    </div>
</body>
</html>