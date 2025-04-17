<?php
include('funkcja.php');
session_start();

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}

function wypisz_Samochody($filter_field = null, $filter_value = null) {
    global $polaczenie;

    $zapytanie = "SELECT samochody.id AS samochody_id, Concat(klienci.imie, ' ', klienci.nazwisko) AS Wlasciciel, marka, model, rok, numer_rejestracyjny FROM `samochody` Join klienci On klienci.id = samochody.id_klienta";
    if ($filter_field && $filter_value) {
        $zapytanie .= " WHERE $filter_field LIKE '%$filter_value%'";
    }

    $wynik = mysqli_query($polaczenie, $zapytanie);

    $naglowek = array("Właścicie", "Marka", "Model", "Rok Produkcji", "Numer Rejestracyjny");
    print("<center>");
    print("<form method='POST' class='client-form'>");
    print("<div class='search-section'>");
    print("<label for='filter_field'>Wyszukaj samochód po: </label>");
    print("<select name='filter_field' class='search-select'>");
    print("<option value='marka'>Marka</option>");
    print("<option value='model'>Model</option>");
    print("<option value='rok'>Rok produkcji</option>");
    print("<option value='numer_rejestracyjny'>Numer rejestracyjny</option>");
    print("</select>");
    print(" <input type='text' name='filter_value' placeholder='Wpisz wartość' class='search-input'>");
    print(" <input type='submit' name='search' value='Szukaj' class='btn'>");
    print(" <input type='submit' name='refresh' value='Odśwież' class='btn'>");
    print("<input type='submit' name='przycisk[-1]' value='Nowy Samochod' class='btn'><br><br>");
    print("</div>");
    print("</center>");

    print("<table class='client-table'><tr>");
    foreach($naglowek as $naglowek)
        print("<th>$naglowek</th>");
    print("<th>Akcje</th>");    
    print("</tr>");

    while($wiersz = mysqli_fetch_row($wynik)) {        
        print("<tr>");
        foreach($wiersz as $p => $pole)
            if($p != 0) print("<td>" . htmlspecialchars($pole) . "</td>");
        print("<td><input type='submit' name='przycisk[$wiersz[0]]' value='Edytuj' class='btn'>
                 <input type='submit' name='przycisk[$wiersz[0]]' value='Usuń' class='btn'></td>");    
        print("</tr>");        
    }
    print("</table>");
    print("</form>");

    mysqli_free_result($wynik);
}

function usun_samochod($nr) {
    global $polaczenie;

    $zapytanie = "DELETE FROM samochody WHERE id=$nr;";
    mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");
}

function edytuj_samochod($nr = -1, $id_klienta = '', $marka = '', $model = '', $rok = '', $numer_rejestracyjny='') {
    global $polaczenie;

    if ($nr != -1) {
        $zapytanie = "SELECT id_klienta, marka, model, rok, numer_rejestracyjny FROM samochody WHERE id=$nr;";
        $rekord = mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");

        $Samochody = mysqli_fetch_row($rekord);
        $id_klienta = $Samochody[0];
        $marka = $Samochody[1];
        $model = $Samochody[2];
        $rok = $Samochody[3];
        $numer_rejestracyjny = $Samochody[4];
    } else {
        $id_klienta = "";
        $marka = "";
        $model = "";
        $rok = "";
        $numer_rejestracyjny = "";
    }

    $klienci = mysqli_query($polaczenie, "Select id, Concat(imie, ' ', nazwisko) AS Wlascisie FROM klienci");
?>

<form method=Post action='' class='client-form'>
<table class='form-table'>
    <tr>
    <td>Wlascicie</td><td colspan=2>
     <select name="id_klienta" class="input-field">
        <?php
            while ($klient = mysqli_fetch_assoc($klienci)) {
                $selected = ($klient['id'] == $id_klienta) ? 'selected' : '';
                echo "<option value='{$klient['id']}' $selected>{$klient['Wlascisie']}</option>";
            }
        ?>
     </select>
    </td>
    </tr>
    <tr>
    <td>Marka</td><td colspan=2>
    <input type=text name='marka' value='<?=$marka?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Model</td><td colspan=2>
    <input type=text name='model' value='<?=$model?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Rok Produkcji</td><td colspan=2>
    <input type=numer name='rok' value='<?=$rok?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Numer Rejestracyjny</td><td colspan=2>
    <input type=numer name='numer_rejestracyjny' value='<?=$numer_rejestracyjny?>' class='input-field'></td>
    </tr>
    <tr>
    <td colspan=3>
    <input type=submit name='przycisk[<?=$nr?>]' value='Zapisz' class='btn'></td>
    </tr>
</table>
</form>
<?php
}

function zapisz_samochod($nr) {
    global $polaczenie;

    $id_klienta = isset($_POST['id_klienta']) ? trim($_POST['id_klienta']) : '';
    $marka = isset($_POST['marka']) ? trim($_POST['marka']) : '';
    $model = isset($_POST['model']) ? trim($_POST['model']) : '';
    $rok = isset($_POST['rok']) ? trim($_POST['rok']) : '';
    $numer_rejestracyjny = isset($_POST['numer_rejestracyjny']) ? trim($_POST['numer_rejestracyjny']) : '';

    $errors = [];

    if (empty($id_klienta)) {
        $errors[] = "Pole 'Wlascicie' jest wymagane.";
    }

    if (empty($marka)) {
        $errors[] = "Pole 'Marka' jest wymagane.";
    }

    if (empty($model)) {
        $errors[] = "Pole 'Model' jest wymagane.";
    }

    if (!is_numeric($rok) || $rok < 1980 || $rok > date("Y")) {
        $errors[] = "Pole 'Rok' musi być liczbą pomiędzy 1980 a bieżącym rokiem.";
    }

    if (empty($numer_rejestracyjny)) {
        $errors[] = "Pole 'Numer rejestracyjny' jest wymagane.";
    }

    if (!empty($errors)) {
        echo "<center><div class='error-messages' style='color: red;'>";
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
        echo "</div></center>";
        
        edytuj_samochod($nr, $id_klienta, $marka, $model, $rok, $numer_rejestracyjny);
        return; 
    }

    if ($nr != -1) {
        $rozkaz = "UPDATE samochody 
                   SET id_klienta='$id_klienta', marka='$marka', model='$model', 
                       rok='$rok', numer_rejestracyjny='$numer_rejestracyjny' 
                   WHERE id=$nr;";
    } else {
        $rozkaz = "INSERT INTO samochody (id_klienta, marka, model, rok, numer_rejestracyjny) 
                   VALUES ('$id_klienta', '$marka', '$model', '$rok', '$numer_rejestracyjny');";
    }

    if (!mysqli_query($polaczenie, $rozkaz)) {
        exit("Błąd zapytania SQL: " . mysqli_error($polaczenie));
    }

    header("Location: Samochody.php");
    exit;
}

?>

<html>
<head>
<meta charset="utf-8">
<title>Obsługa Samochodu</title>
<link rel="stylesheet" href="includes/style.css">
</head>


<?php
include('includes/header.php');
if ($_POST) {
    error_reporting(0); 
}

$polecenie = '';
if (isset($_POST['przycisk'])) {
    $nr = key($_POST['przycisk']);
    $polecenie = $_POST['przycisk'][$nr];
} 

$filter_field = null;
$filter_value = null;
if (isset($_POST['search'])) {
    $filter_field = $_POST['filter_field'];
    $filter_value = $_POST['filter_value'];
}

if (isset($_POST['refresh'])) {
    $filter_field = null;
    $filter_value = null;
}

otworz_polaczenie();

switch($polecenie) {
    case 'Edytuj': edytuj_samochod($nr); break;
    case 'Nowy Samochod': edytuj_samochod(); break;
    case 'Zapisz': zapisz_samochod($nr); break;
    case 'Usuń': usun_samochod($nr); break;
}

wypisz_Samochody($filter_field, $filter_value);
zamknij_polaczenia();
?>


</body>
</html>
