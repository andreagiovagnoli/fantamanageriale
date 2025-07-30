<!-- login.php - Pagina di login per gli utenti -->
<?php
session_start(); // Inizia la sessione PHP
require_once 'db_connection.php'; // Include il file di connessione al database

$error = ''; // Variabile per memorizzare eventuali messaggi di errore

// Controlla se il modulo di login è stato inviato (metodo POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepara la query SQL per selezionare l'utente dal database
    // Usiamo istruzioni preparate per prevenire SQL Injection
    $sql = "SELECT * FROM utenti WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // 's' indica che il parametro è una stringa
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // In un'applicazione reale, dovresti sempre usare password_verify()
        // per confrontare password hashate, es: if (password_verify($password, $user['password']))
        if ($password == $user['password']) { // Per questo esempio, confrontiamo la password in chiaro
            $_SESSION['loggedin'] = true; // Imposta la variabile di sessione per indicare che l'utente è loggato
            $_SESSION['username'] = $username; // Salva il nome utente nella sessione
            header("Location: dashboard.php"); // Reindirizza l'utente alla pagina della dashboard
            exit; // Termina lo script dopo il reindirizzamento
        } else {
            $error = "Password errata."; // Messaggio di errore per password non corrispondente
        }
    } else {
        $error = "Nome utente non trovato."; // Messaggio di errore per utente non esistente
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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Accedi</h2>
        <?php if ($error): ?>
            <p class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-semibold mb-2">Nome Utente:</label>
                <input type="text" id="username" name="username" required
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                       class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200">
                Login
            </button>
        </form>
    </div>
</body>
</html>