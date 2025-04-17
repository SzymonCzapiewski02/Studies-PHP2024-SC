<?php
    include('funkcja.php');
    session_start();

    if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
        header("Location: logowanie.php");
        exit();
    }

    otworz_polaczenie();

    function harmonogram_mechanika($id, $miesiac, $rok) {
        global $polaczenie;

        $zapytanie = "SELECT imie, nazwisko, dni_robocze_od, dni_robocze_do, weekend_od, weekend_do FROM mechanicy WHERE id = $id";

        $wynik = mysqli_query($polaczenie, $zapytanie) or exit("bład w zapytaniu");

        $mechanik = mysqli_fetch_row($wynik);

        $dni_miesiaca = cal_days_in_month(CAL_GREGORIAN, $miesiac, $rok);
        $pierszy_dzien = date('N', strtotime("$rok-$miesiac-01"));
        $aktualny_dzien = 1;

        $dni_robocze_od = $mechanik['2'];
        $dni_robocze_do = $mechanik['3'];
        $weekend_od = $mechanik['4'];
        $weekend_do = $mechanik['5'];

        $imie = htmlspecialchars($mechanik['0']);
        $nazwisko = htmlspecialchars($mechanik['1']);

        print("<h2 class='h1Style'>Harmonogram Pracownika: $imie $nazwisko w Miesiacu $miesiac/$rok</h2>");
        print("<div class='calendar'>");
        print("<table class='calendar-table'>");
        $dni_tygodnia =  ['Pn', 'Wt', 'Śr', 'Czw', 'Pt', 'Sb', 'Nd'];
        print("<tr>");
        foreach ($dni_tygodnia as $dzien) {
            print("<th>$dzien</th>");
        }
        print("</tr><tr>");
        for ($i = 1; $i < $pierszy_dzien; $i++) {
            print("<td></td>");
        }
        while ($aktualny_dzien <= $dni_miesiaca) {
            $dzien_tygodnia = date('N', strtotime("$rok-$miesiac-$aktualny_dzien"));
            $dzien = $aktualny_dzien < 10 ? "0$aktualny_dzien" : $aktualny_dzien;
            $data = "$rok-$miesiac-$dzien";

            print("<td>");
            if ($dzien_tygodnia >= 1 && $dzien_tygodnia <= 5) { 
                print("<b>Dni robocze</b><br>");
                print("Godziny: $dni_robocze_od - $dni_robocze_do");
            } elseif ($dzien_tygodnia == 6 || $dzien_tygodnia == 7) {
                if ($weekend_od && $weekend_do) {
                    print("<b>Weekend</b><br>");
                    print("Godziny: $weekend_od - $weekend_do");
                } else {
                    print("<b>Nie pracuje</b>");
                }
            }
            print("</td>");
            if ($dzien_tygodnia == 7 && $aktualny_dzien != $dni_miesiaca) {
                print("</tr><tr>");
            }
            $aktualny_dzien++;
        }
        $ostatni_dzien_tygodnia = date('N', strtotime("$rok-$miesiac-$dni_miesiaca"));
        for ($i = $ostatni_dzien_tygodnia; $i < 7; $i++) {
            print("<td></td>");
        }

        print("</tr>");
        print("</table>");
        print("</div>");
    }
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harmonogram Mechanika</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <h1 class="h1Style">Harmonogram Pracy Mechanika</h1>

    <form class="logowanie" method="POST" action="">
        <label class="napisz" for="mechanik">Wybierz mechanika:</label>
        <select class="pole" id="mechanik" name="mechanik" required>
            <?php
                $zapytanie = "SELECT id, imie, nazwisko FROM mechanicy";
                $wynik = mysqli_query($polaczenie, $zapytanie) or exit("Błąd w zapytaniu: $zapytanie");

                while ($mechanik = mysqli_fetch_assoc($wynik)) {
                    $id = htmlspecialchars($mechanik['id']);
                    $imie = htmlspecialchars($mechanik['imie']);
                    $nazwisko = htmlspecialchars($mechanik['nazwisko']);
                    print("<option value='$id'>$imie $nazwisko</option>");
                }
            ?>
        </select>

        <label class="napisz" for="miesiac">Miesiąc:</label>
        <input type="number" class="pole" id="miesiac" name="miesiac" min="1" max="12" required><br>

        <label class="napisz" for="rok">Rok:</label>
        <input type="number" class="pole" id="rok" name="rok" min="2000" max="2100" required><br>

        <input type="submit" value="Generuj harmonogram" class="btn">
    </form>

    <div>
    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mechanik_id = $_POST['mechanik'] ?? null;
            $miesiac = $_POST['miesiac'] ?? null;
            $rok = $_POST['rok'] ?? null;

            if (is_numeric($mechanik_id) && is_numeric($miesiac) && is_numeric($rok)) {
                harmonogram_mechanika($mechanik_id, $miesiac, $rok);
            } else {
                print("<h2>Proszę wprowadzić poprawne dane.</h2>");
            }
        }
        zamknij_polaczenia();
    ?>
    </div>

</body>
</html>
