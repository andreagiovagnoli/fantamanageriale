<!-- bilanci.php - Pagina dedicata ai Bilanci -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';
// Qui potrai aggiungere la logica specifica per la sezione Bilanci
$conn->close();
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
    </style>
</head>
<body class="p-6">
    <div class="container mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Bilanci</h2>
        <p class="text-gray-700 mb-6">Questa pagina mostrerà i bilanci e le statistiche finanziarie della tua squadra. Qui potrai visualizzare grafici, entrate, uscite e proiezioni.</p>
        <a href="dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">Torna alla Dashboard</a>
    </div>
</body>
</html>