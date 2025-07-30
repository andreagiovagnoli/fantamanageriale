<!-- db_connection.php - File per la connessione al database -->
<?php
// Dettagli di connessione al database
$servername = "localhost";
$username = "root"; // Utente di default di XAMPP per MySQL
$password = "";     // Password di default di XAMPP per MySQL è vuota
$dbname = "fantamanageriale"; // Il nome del database che hai creato in phpMyAdmin

// Crea la connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    // Se la connessione fallisce, termina lo script e mostra un messaggio di errore
    die("Connessione fallita: " . $conn->connect_error);
}
?>