<?php
    include('funkcja.php');
    session_start();
    
    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
        header("Location: logowanie.php"); 
        exit();
    }

    otworz_polaczenie();
    
    function raport($miesiac, $rok) {
        global $polaczenie;
    
        $zapytanie = "SELECT naprawy.id, samochody.marka, samochody.model, klienci.imie, klienci.nazwisko, naprawy.koszt, naprawy.status
        FROM naprawy
        JOIN samochody ON naprawy.id_samochodu = samochody.id
        JOIN klienci ON samochody.id_klienta = klienci.id
        WHERE MONTH(naprawy.data_rozpoczecia) = $miesiac AND YEAR(naprawy.data_rozpoczecia) = $rok";
    
        $wynik = mysqli_query($polaczenie, $zapytanie);
    
        if (!$wynik || mysqli_num_rows($wynik) == 0) {
            print("<h2>Brak wyników dla podanego miesiąca i roku.</h2>");
            return;
        }
    
        $naglowek = array("Marka", "Model", "Imię Właściciela", "Nazwisko Właściciela", "Koszt", "Status");
    
        print("<h1 class='h1Style'>Raport napraw za $miesiac/$rok</h1>");
        print("<table class='client-table'><tr>");
        foreach ($naglowek as $naglowekKolumny) {
            print("<th>" . htmlspecialchars($naglowekKolumny) . "</th>");
        }
        print("</tr>");
    
        while ($wiersz = mysqli_fetch_row($wynik)) {        
            print("<tr>");
            foreach ($wiersz as $p => $pole) {
                if ($p != 0) print("<td>" . htmlspecialchars($pole) . "</td>");
            }
            print("</tr>");        
        }
        print("</table>");
        $zapytanie2 = "SELECT SUM(koszt) AS suma_kosztow FROM naprawy Where MONTH(naprawy.data_rozpoczecia) = $miesiac AND YEAR(naprawy.data_rozpoczecia) = $rok;";
        $wynik2 = mysqli_query($polaczenie, $zapytanie2);

        if ($wynik2) {
            $wiersz2 = mysqli_fetch_row($wynik2);
            $sumaKosztow = $wiersz2[0] ?? 0;
            print("<h3>Suma Kosztów: ".htmlspecialchars(number_format($sumaKosztow, 2))."</h3>");
        } else {
            print("<h3>Nie udało się obliczyć sumy kosztów.</h3>");
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <h1 class="h1Style">Generowanie Raportu Napraw</h1>
    <form class="logowanie" method="POST" action="">
        <label class="napisz" for="miesiac">Miesiac</label>
        <input type="number" class="pole" id="miesiac" name="miesiac" min="1" max="12" require><br>
        <label class="napisz" for="Rok">Rok</label>
        <input type="number" class="pole" id="rok" name="rok" min="2000" max="2100" require><br>
        <input type="submit" value="Generuj raport" class="btn">
    </form>
    <div>
    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $miesiac = $_POST['miesiac'] ?? null;
            $rok = $_POST['rok'] ?? null;

            if (is_numeric($miesiac) && is_numeric($rok)) {
                raport($miesiac, $rok);
            } else {
                print("<h2>Proszę wprowadzić poprawny miesiąc i rok.</h2>");
            }
        }
        zamknij_polaczenia();
        ?>
    </div>
</body>
</html>