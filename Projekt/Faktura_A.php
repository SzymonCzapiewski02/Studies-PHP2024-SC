<?php
include('funkcja.php');
session_start();
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}
otworz_polaczenie();

function generujDane() {
    global $polaczenie;
    $zapytanie = "SELECT id, imie, nazwisko FROM klienci";
    $wynik = mysqli_query($polaczenie, $zapytanie) or exit("Błąd w zapytaniu: $zapytanie");
    while ($klienci = mysqli_fetch_row($wynik)) {
        echo "<option value='$klienci[0]'>$klienci[1] $klienci[2]</option>";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktura</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <h1 class="h1Style">Generowanie Faktury</h1>
    <form class="logowanie" method="POST" action="Faktura_B.php">
        <label class="napisz" for="id_klienta">Wybierz Klienta:</label>
        <select class="pole" id="id_klienta" name="id_klienta" required>
            <?php generujDane(); ?>
        </select><br>

        <label class="napisz" for="Platnosc">Wybierz Płatność:</label>
        <select class="pole" id="Platnosc" name="Platnosc" required>
            <option value='gotówka'>Gotówka</option>
            <option value='blik'>Blik</option>
            <option value='przelew'>Przelew</option>
            <option value='PayPal'>PayPal</option>
        </select>

        <label class="napisz" for="Kraj">Kraj:</label>
        <input type="text" class="pole" name="Kraj" value="<?= $_SESSION['kraj'] ?? '' ?>" required><br>
        <label class="napisz" for="NP">Kod pocztowy:</label>
        <input type="text" class="pole" name="NP" value="<?= $_SESSION['numer_pocztowy'] ?? '' ?>" required><br>
        <label class="napisz" for="Miasto">Miasto:</label>
        <input type="text" class="pole" name="Miasto" value="<?= $_SESSION['miasto'] ?? '' ?>" required><br>
        <label class="napisz" for="Ulica">Ulica:</label>
        <input type="text" class="pole" name="Ulica" value="<?= $_SESSION['ulica'] ?? '' ?>" required><br>
        <input type="submit" class="btn" value="Generuj Fakturę">
    </form>
    <?php zamknij_polaczenia(); ?>
</body>
</html>
