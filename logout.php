<!-- logout.php - Pagina per effettuare il logout -->
<?php
session_start(); // Inizia la sessione
session_unset(); // Rimuove tutte le variabili di sessione
session_destroy(); // Distrugge la sessione
header("Location: login.php"); // Reindirizza l'utente alla pagina di login
exit; // Termina lo script
?>