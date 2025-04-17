<?php
include('funkcja.php');
session_start();

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}

function wyswietl_kalendarz() {
    global $polaczenie;

    print("<div class='search-section'>");
    print("<form method='GET' class='client-form'>");
    print("<label for='miesiac'>Miesiac(01-12): </label>");
    print(" <input type='number' name='miesiac' required class='search-input'>");
    print("<label for='rok'> Rok(YYYY): </label>");
    print(" <input type='number' name='rok' required class='search-input'>");
    print("<input type='submit' value='Pokaż naprawy' class='btn'>");
    print("</form>");
    print("</div>");

    $miesiac = isset($_GET['miesiac']) ? $_GET['miesiac'] : date('m'); 
    $rok = isset($_GET['rok']) ? $_GET['rok'] : date('Y');

    $dni_miesiaca = cal_days_in_month(CAL_GREGORIAN, $miesiac, $rok);

    $zapytanie = "SELECT naprawy.id AS naprawa_id, CONCAT(mechanicy.imie, ' ', mechanicy.nazwisko) AS mechanik, naprawy.data_rozpoczecia, naprawy.data_zakonczenia, naprawy.koszt
                  FROM naprawy, mechanicy
                  WHERE mechanicy.id = naprawy.id_mechanika && YEAR(naprawy.data_rozpoczecia) = '$rok' 
                  && MONTH(naprawy.data_rozpoczecia) = '$miesiac'";

    $wynik = mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");

    $naprawy_kalendarz = [];

    while ($wiersz = mysqli_fetch_row($wynik)) {
        $data_R = $wiersz[2];
        $data_Z = $wiersz[3];
        $naprawy_kalendarz[$data_R][] = [
            'mechanik' => $wiersz['1'],
            'koszt' => $wiersz['4'],
            'data_zakonczenia' => $data_Z
        ];
    }

    print("<div class='calendar'>");
    print("<h2 class='h1Style'>Kalendarz Napraw</h2>");
    print("<table class='calendar-table'><tr>");

    for ($i = 1; $i <= $dni_miesiaca; $i++) {
        print("<th>$i</th>");
    }
    $aktualny_miesiac = $rok . "-" . ($miesiac < 10 ? '0' . $miesiac : $miesiac);
    print("</tr><tr>");
    for ($i = 1; $i <= $dni_miesiaca; $i++) {
        $dzien = $i < 10 ? '0' . $i : $i;
        $data = "$aktualny_miesiac-$dzien";
        print("<td>");
        if (isset($naprawy_kalendarz[$data])) {
            foreach ($naprawy_kalendarz[$data] as $naprawa) {
                print("<div class='repair-item'>");
                print("<b>Mechanik</b>: ".$naprawa['mechanik']."<br>");
                print("<b>Koszt</b>: ".$naprawa['koszt']."<br>");
                print("<b>Data Zakonczenia</b>: ".$naprawa['data_zakonczenia']);
                print("</div>");
            }
        }
    }
}

function wypisz_Naprawy($filter_field = null, $filter_value = null) {
    global $polaczenie;

    $zapytanie = "SELECT naprawy.id AS naprawa_id, CONCAT(samochody.marka, ' ', samochody.model) AS samochod, CONCAT(mechanicy.imie, ' ', mechanicy.nazwisko) AS mechanik, naprawy.data_rozpoczecia, naprawy.data_zakonczenia, naprawy.koszt, naprawy.status
                    FROM naprawy
                    JOIN samochody ON samochody.id = naprawy.id_samochodu
                    JOIN mechanicy ON mechanicy.id = naprawy.id_mechanika";
    if ($filter_field && $filter_value) {
        $zapytanie .= " WHERE $filter_field LIKE '%$filter_value%'";
    }

    $wynik = mysqli_query($polaczenie, $zapytanie);

    $naglowek = array("Samochód", "Mechanik", "Data Rozpoczencia", "Data Zakonczenia", "Koszt", "Status");
    print("<center>");
    print("<form method='POST' class='client-form'>");
    print("<div class='search-section'>");
    print("<label for='filter_field'>Wyszukaj naprawe po: </label>");
    print("<select name='filter_field' class='search-select'>");
    print("<option value='samochody.marka'>Marka samochodu</option>");
    print("<option value='samochody.model'>Model samochodu</option>");
    print("<option value='mechanicy.imie'>Imię mechanika</option>");
    print("<option value='mechanicy.nazwisko'>Nazwisko mechanika</option>");
    print("</select>");
    print(" <input type='text' name='filter_value' placeholder='Wpisz wartość' class='search-input'>");
    print(" <input type='submit' name='search' value='Szukaj' class='btn'>");
    print(" <input type='submit' name='refresh' value='Odśwież' class='btn'>");
    print("<input type='submit' name='przycisk[-1]' value='Nowa Naprawa' class='btn'><br><br>");
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

function usun_naprawe($nr) {
    global $polaczenie;

    $zapytanie = "DELETE FROM naprawy WHERE id=$nr;";
    mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");
}

function edytuj_naprawy($nr = -1, $id_samochodu = '', $id_mechanika = '', $data_rozpoczenia = '', $data_zakonczenia = '', $koszt='', $status ='') {
    global $polaczenie;

    if ($nr != -1) {
        $zapytanie = "SELECT id_samochodu, id_mechanika, data_rozpoczecia, data_zakonczenia, koszt, status FROM naprawy WHERE id=$nr;";
        $rekord = mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");

        $Naprawy = mysqli_fetch_row($rekord);
        $id_samochodu = $Naprawy[0];
        $id_mechanika = $Naprawy[1];
        $data_rozpoczenia = $Naprawy[2];
        $data_zakonczenia = $Naprawy[3];
        $koszt = $Naprawy[4];
        $status = $Naprawy[5];
    } else {
        $id_samochodu = "";
        $id_mechanika = "";
        $data_rozpoczenia = "";
        $data_zakonczenia = "";
        $koszt = "";
        $status = "";
    }

    $samochody = mysqli_query($polaczenie, "Select id, Concat(marka, ' ', model) AS samochod FROM samochody");
    $mechanicy = mysqli_query($polaczenie, "Select id, Concat(imie, ' ', nazwisko) AS mechanik FROM mechanicy");
?>

<form method=Post action='' class='client-form'>
<table class='form-table'>
    <tr>
    <td>Samochód</td><td colspan=2>
     <select name="id_samochodu" class="input-field">
        <?php
            while ($samochod = mysqli_fetch_assoc($samochody)) {
                $selected = ($samochod['id'] == $id_samochodu) ? 'selected' : '';
                echo "<option value='{$samochod['id']}' $selected>{$samochod['samochod']}</option>";
            }
        ?>
     </select>
    </td>
    </tr>
    <tr>
    <td>Mechanik</td><td colspan=2>
     <select name="id_mechanika" class="input-field">
        <?php
            while ($mechanik = mysqli_fetch_assoc($mechanicy)) {
                $selected = ($mechanik['id'] == $id_mechanika) ? 'selected' : '';
                echo "<option value='{$mechanik['id']}' $selected>{$mechanik['mechanik']}</option>";
            }
        ?>
     </select>
    </td>
    </tr>
    <tr>
    <td>Data Rozpoczęcia</td><td colspan=2>
    <input type=date name='data_rozpoczenia' value='<?=$data_rozpoczenia?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Data Zakonczenia</td><td colspan=2>
    <input type=date name='data_zakonczenia' value='<?=$data_zakonczenia?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Koszt</td><td colspan=2>
    <input type=numer name='koszt' value='<?=$koszt?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Status</td><td colspan=2>
    <select name="status" class="input-field">
    <option value="w trakcie" <?= $status === 'w trakcie' ? 'selected' : '' ?>>W trakcie</option>
    <option value="zakonczona" <?= $status === 'zakonczona' ? 'selected' : '' ?>>Zakończona</option>
    <option value="oczekuje" <?= $status === 'oczekuje' ? 'selected' : '' ?>>Oczekuje</option>
    </select>
    </td>
    </tr>
    <tr>
    <td colspan=3>
    <input type=submit name='przycisk[<?=$nr?>]' value='Zapisz' class='btn'></td>
    </tr>
</table>
</form>
<?php
}

function zapisz_naprawe($nr) {
    global $polaczenie;

    $id_samochodu = isset($_POST['id_samochodu']) ? trim($_POST['id_samochodu']) : '';
    $id_mechanika = isset($_POST['id_mechanika']) ? trim($_POST['id_mechanika']) : '';
    $data_rozpoczenia = isset($_POST['data_rozpoczenia']) ? trim($_POST['data_rozpoczenia']) : '';
    $data_zakonczenia = isset($_POST['data_zakonczenia']) ? trim($_POST['data_zakonczenia']) : '';
    $koszt = isset($_POST['koszt']) ? trim($_POST['koszt']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $errors = [];

    if (empty($id_samochodu)) {
        $errors[] = "Pole 'Samochod' jest wymagane.";
    }

    if (empty($id_mechanika)) {
        $errors[] = "Pole 'Mechanik' jest wymagane.";
    }

    if (empty($data_rozpoczenia) || $data_rozpoczenia > $data_zakonczenia) {
        $errors[] = "Pole 'Data rozpoczęcia' musi być wcześniejsze niż 'Data zakończenia'.";
    }
    
    if ($data_zakonczenia <= $data_rozpoczenia) {
        $errors[] = "Pole 'Data zakończenia' musi być późniejsze niż 'Data rozpoczęcia'.";
    }

    if (!is_numeric($koszt) || $koszt < 0) {
        $errors[] = "Pole 'Koszt' musi być liczbą większą lub równą 0.";
    }

    if (!in_array($status, ['w trakcie', 'zakonczona', 'oczekuje'])) {
        $errors[] = "Pole 'Status' musi być jedną z opcji: 'w trakcie', 'zakończona', 'oczekuje'.";
    }

    if (!empty($errors)) {
        echo "<center><div class='error-messages' style='color: red;'>";
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
        echo "</div></center>";
        
        edytuj_naprawy($nr, $id_samochodu, $id_mechanika, $data_rozpoczenia, $data_zakonczenia, $koszt, $status);
        return; 
    }

    if ($nr != -1) {
        $rozkaz = "UPDATE naprawy 
                   SET id_samochodu='$id_samochodu', id_mechanika='$id_mechanika', data_rozpoczecia='$data_rozpoczenia', 
                       data_zakonczenia='$data_zakonczenia', koszt='$koszt', status='$status' 
                   WHERE id=$nr;";
    } else {
        $rozkaz = "INSERT INTO naprawy (id_samochodu, id_mechanika, data_rozpoczecia, data_zakonczenia, koszt, status) 
                   VALUES ('$id_samochodu', '$id_mechanika', '$data_rozpoczenia', '$data_zakonczenia', '$koszt', '$status');";
    }

    if (!mysqli_query($polaczenie, $rozkaz)) {
        exit("Błąd zapytania SQL: " . mysqli_error($polaczenie));
    }

    header("Location: Naprawy.php");
    exit;
}

?>

<html>
<head>
<meta charset="utf-8">
<title>Obsługa Napraw</title>
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
    case 'Edytuj': edytuj_naprawy($nr); break;
    case 'Nowa Naprawa': edytuj_naprawy(); break;
    case 'Zapisz': zapisz_naprawe($nr); break;
    case 'Usuń': usun_naprawe($nr); break;
}

wypisz_Naprawy($filter_field, $filter_value);

wyswietl_kalendarz();

zamknij_polaczenia();
?>

</body>
</html>
