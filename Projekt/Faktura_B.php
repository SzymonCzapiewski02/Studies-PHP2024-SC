<?php
include('funkcja.php');
session_start();

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}

otworz_polaczenie();
$plik = 'file\faktury.txt';

function zapiszFakture($daneFaktury, $plik) {
    $nowyPlik = !file_exists($plik); 
    $open = fopen($plik, 'a'); 
    if ($nowyPlik) {
        fwrite($open, "Imie;Nazwisko;Marka;Model;Data_zakonczenia;Kwota;Platnosc;Kraj;Kod_pocztowy;Miasto;Ulica\n");
    }
    fwrite($open, implode(';', $daneFaktury) . "\n");
    fclose($open);
}

function wczytajFaktury($plik) {
    $faktury = [];
    if (file_exists($plik)) {
        $open = fopen($plik, 'r'); 
        fgetcsv($open, 0, ';'); 
        while (($data = fgetcsv($open, 0, ';')) !== false) {
            $faktury[] = $data; 
        }
        fclose($open);
    }
    return $faktury;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_klienta = $_POST['id_klienta'];
    $platnosc = $_POST['Platnosc'];
    $kraj = $_POST['Kraj'];
    $numer_pocztowy = $_POST['NP'];
    $miasto = $_POST['Miasto'];
    $ulica = $_POST['Ulica'];
    $_SESSION['id_klienta'] = $id_klienta;
    $_SESSION['platnosc'] = $platnosc;
    $_SESSION['kraj'] = $kraj;
    $_SESSION['numer_pocztowy'] = $numer_pocztowy;
    $_SESSION['miasto'] = $miasto;
    $_SESSION['ulica'] = $ulica;

    global $polaczenie;
    $zapytanie = "
        SELECT klienci.imie, klienci.nazwisko, samochody.marka, samochody.model, naprawy.data_zakonczenia, naprawy.koszt 
        FROM naprawy JOIN samochody ON naprawy.id_samochodu = samochody.id JOIN klienci ON samochody.id_klienta = klienci.id WHERE klienci.id = $id_klienta";

    $wynik = mysqli_query($polaczenie, $zapytanie) or exit("Błąd w zapytaniu: $zapytanie");
    
    while ($row = mysqli_fetch_row($wynik)) {
        $daneFaktury = [
            $row[0], 
            $row[1], 
            $row[2], 
            $row[3], 
            $row[4], 
            $row[5], 
            $platnosc, 
            $kraj, 
            $numer_pocztowy, 
            $miasto, 
            $ulica 
        ];
        zapiszFakture($daneFaktury, $plik);
    }

    header("Location: Faktura_B.php");
    exit();
}

$faktury = wczytajFaktury($plik);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktury</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <h1 class="h1Style">Lista Wszystkich Faktur</h1>
    <?php if (!empty($faktury)) {
        print("<table class='client-table'><thead><tr>");
        print("<th>Imię</th><th>Nazwisko</th><th>Marka</th><th>Model</th><th>Data zakończenia</th><th>Koszt</th>
                <th>Płatność</th><th>Kraj</th><th>Kod pocztowy</th><th>Miasto</th><th>Ulica</th>");
        print("</tr></thead><tbody>");
                foreach ($faktury as $faktura) {
                    print("<tr>");
                         foreach ($faktura as $value) {
                            print("<td>$value</td>");
                         }
                    print("</tr>");
                    }
                 } ?>
            </tbody>
        </table>
    <?= zamknij_polaczenia(); ?>
    <form method="post" action="Faktura_A.php">
        <input type="submit" value="Powrót do generacji Faktór" class="btn">
    </form>
</body>
</html>
