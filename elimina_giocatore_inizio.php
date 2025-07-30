<!-- elimina_giocatore_inizio.php - Pagina per eliminare un giocatore in Rosa Inizio Stagione (Accessibile a tutti gli utenti) -->
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_connection.php';

$username_logged_in = $_SESSION['username'];
$is_admin = ($username_logged_in === 'admin');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Recupera i dati del giocatore prima dell'eliminazione
    $player_to_delete = null;
    $sql_select_player = "SELECT NOME, PROPRIETARIO FROM giocatori WHERE ID = ?";
    $stmt_select_player = $conn->prepare($sql_select_player);
    $stmt_select_player->bind_param("i", $id);
    $stmt_select_player->execute();
    $result_select_player = $stmt_select_player->get_result();
    if ($result_select_player->num_rows == 1) {
        $player_to_delete = $result_select_player->fetch_assoc();
    }
    $stmt_select_player->close();

    // Logica per l'eliminazione: admin può eliminare qualsiasi giocatore, utente normale solo i propri
    $sql = "DELETE FROM giocatori WHERE ID = ?";
    if (!$is_admin) {
        $sql .= " AND PROPRIETARIO = ?"; // Aggiunge la condizione per gli utenti non-admin
    }
    
    $stmt = $conn->prepare($sql);
    if ($is_admin) {
        $stmt->bind_param("i", $id);
    } else {
        $stmt->bind_param("is", $id, $username_logged_in);
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Logga l'azione SOLO SE l'utente è un admin
            if ($is_admin && $player_to_delete) {
                $descrizione = "Giocatore '{$player_to_delete['NOME']}' (ID: {$id}, Proprietario: {$player_to_delete['PROPRIETARIO']}) eliminato tramite 'Rosa Inizio Stagione'.";
                $log_sql = "INSERT INTO storico_modifiche (ID_GIOCATORE, AZIONE, DESCRIZIONE_MODIFICA, UTENTE_ADMIN) VALUES (?, ?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $azione = 'ELIMINAZIONE';
                $log_stmt->bind_param("isss", $id, $azione, $descrizione, $username_logged_in);
                $log_stmt->execute();
                $log_stmt->close();
            }
            header("Location: rosa_inizio_stagione.php");
            exit;
        } else {
            // Se affected_rows è 0, significa che il giocatore non è stato trovato
            // o l'utente non-admin ha tentato di eliminare un giocatore non suo.
            echo "<p style='color: red; text-align: center; margin-top: 20px;'>Errore: Giocatore non trovato o non autorizzato a eliminarlo.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center; margin-top: 20px;'>Errore nell'eliminazione del giocatore: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red; text-align: center; margin-top: 20px;'>ID giocatore non specificato per l'eliminazione.</p>";
}
$conn->close();
?>