<!-- dashboard.php - Pagina principale dopo il login -->
<?php
session_start(); // Inizia la sessione
// Controlla se l'utente è loggato, altrimenti reindirizza alla pagina di login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php'; // Include il file di connessione al database

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin'); // Controlla se l'utente è l'admin

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Fantamanageriale</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        /* Stili per le sezioni cliccabili */
        .section-card {
            display: block; /* Rende il link un blocco per occupare lo spazio del div */
            text-decoration: none; /* Rimuove la sottolineatura predefinita del link */
            color: inherit; /* Mantiene il colore del testo ereditato */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-radius: 0.5rem; /* rounded-lg */
        }
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1); /* shadow-lg */
        }
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md shadow-sm transition duration-200">
                    Logout
                </a>
            </div>
        </div>

        <!-- Sezione BILANCI -->
        <a href="bilanci_overview.php" class="section-card bg-blue-50 p-6 rounded-lg shadow-inner border border-blue-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Bilanci</h3>
            <p class="text-gray-800">Questa sezione sarà dedicata ai bilanci e alle statistiche finanziarie della tua squadra.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Riepilogo entrate e uscite.</li>
                <li>Proiezione stipendi futuri.</li>
                <li>Statistiche sui costi dei giocatori.</li>
            </ul>
        </a>

        <hr class="my-8 border-t border-gray-300">

        <!-- Sezione ROSE -->
        <a href="rose.php" class="section-card bg-purple-50 p-6 rounded-lg shadow-inner border border-purple-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Rose</h3>
            <p class="text-gray-800">Qui potrai gestire le tue rose attive e storiche.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Visualizzazione delle rose per giornata o campionato.</li>
                <li>Confronto tra diverse formazioni.</li>
                <li>Analisi della composizione della squadra.</li>
            </ul>
        </a>

        <hr class="my-8 border-t border-gray-300">

        <!-- Sezione OPERAZIONI POST ASTA -->
        <a href="operazioni_post_asta.php" class="section-card bg-yellow-50 p-6 rounded-lg shadow-inner border border-yellow-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Operazioni Post Asta</h3>
            <p class="text-gray-800">Questa area è per le operazioni di mercato successive all'asta principale.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Gestione di scambi, cessioni e nuovi acquisti.</li>
                <li>Monitoraggio delle offerte e delle trattative.</li>
                <li>Storico delle operazioni effettuate.</li>
            </ul>
        </a>

        <hr class="my-8 border-t border-gray-300">

        <!-- Sezione RINNOVI CONTRATTUALI -->
        <a href="rinnovi_contrattuali.php" class="section-card bg-green-50 p-6 rounded-lg shadow-inner border border-green-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Rinnovi Contrattuali</h3>
            <p class="text-gray-800">Tieni sotto controllo i rinnovi dei contratti dei tuoi giocatori.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Elenco dei giocatori in scadenza.</li>
                <li>Opzioni per il rinnovo o la non conferma.</li>
                <li>Simulazioni dei costi dei rinnovi.</li>
            </ul>
        </a>

        <?php if ($is_admin): ?>
        <hr class="my-8 border-t border-gray-300">
        <!-- Sezione STORICO MODIFICHE (Visibile solo all'Admin) -->
        <a href="storico_modifiche.php" class="section-card bg-red-50 p-6 rounded-lg shadow-inner border border-red-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Storico Modifiche</h3>
            <p class="text-gray-800">Qui puoi visualizzare il registro di tutte le modifiche effettuate ai giocatori.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Traccia aggiunte, modifiche ed eliminazioni.</li>
                <li>Visualizza chi ha effettuato la modifica.</li>
                <li>Monitora l'evoluzione dei dati.</li>
            </ul>
        </a>

        <hr class="my-8 border-t border-gray-300">
        <a href="cambio_ruolo_inizio_stagione.php" class="section-card bg-orange-50 p-6 rounded-lg shadow-inner border border-orange-200 mb-6">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Cambio Ruolo Inizio Stagione</h3>
            <p class="text-gray-800">Qui puoi gestire i cambi di ruolo dei giocatori all'inizio della stagione.</p>
            <ul class="list-disc list-inside mt-4 text-gray-700">
                <li>Modifica massiva dei ruoli.</li>
                <li>Applicazione delle nuove liste ruoli ufficiali.</li>
            </ul>
        </a>
        <?php endif; ?>

    </div>
    <?php $conn->close(); // Chiudi la connessione al database ?>
</body>
</html>
