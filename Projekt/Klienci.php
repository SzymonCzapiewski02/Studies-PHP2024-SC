<?php
include('funkcja.php');
session_start();

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}

function wypisz_Klienta($filter_field = null, $filter_value = null) {
    global $polaczenie;

    $zapytanie = "SELECT * FROM klienci";
    if ($filter_field && $filter_value) {
        $zapytanie .= " WHERE $filter_field LIKE '%$filter_value%'";
    }

    $wynik = mysqli_query($polaczenie, $zapytanie);

    $naglowek = array("Imie", "Nazwisko", "Kontakt", "Email");
    print("<form method='POST' class='client-form'>");
    print("<div class='search-section'>");
    print("<label for='filter_field'>Wyszukaj klienta po: </label>");
    print("<select name='filter_field' class='search-select'>");
    print("<option value='imie'>Imię</option>");
    print("<option value='nazwisko'>Nazwisko</option>");
    print("<option value='kontakt'>Kontakt</option>");
    print("<option value='email'>Email</option>");
    print("</select>");
    print(" <input type='text' name='filter_value' placeholder='Wpisz wartość' class='search-input'>");
    print(" <input type='submit' name='search' value='Szukaj' class='btn'>");
    print(" <input type='submit' name='refresh' value='Odśwież' class='btn'>");
    print("<input type='submit' name='przycisk[-1]' value='Nowy Klient' class='btn'><br><br>");
    print("</div>");

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
                 <input type='submit' name='przycisk[$wiersz[0]]' value='Usuń' class='btn'>
                 <input type='submit' name='przycisk[$wiersz[0]]' value='Zapisz w pliku' class='btn'></td>");    
        print("</tr>");        
    }
    print("</table>");
    print("</form>");

    mysqli_free_result($wynik);
}

function zapisz_klienta_to_pliku($nr) {
    global $polaczenie;
    $plik = fopen("file\dane.txt", "w");

    $zapytanie = "SELECT klienci.imie, klienci.nazwisko, klienci.kontakt, klienci.email, samochody.marka, samochody.model, samochody.rok, samochody.numer_rejestracyjny FROM samochody, klienci WHERE samochody.id_klienta = klienci.id && klienci.id = '$nr';";

    $wynik = mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");

    if($wynik) {
        while ($wiersz = mysqli_fetch_assoc($wynik)) {
            $linia = '';
            foreach ($wiersz as $klucz => $wartosc) {
                $linia .= ucfirst($klucz) . ": $wartosc, \n";
            }
            fwrite($plik, $linia);
            print("<label for='filter_field'>Dane zostały zapisane w pliku: dane.txt</label>");
        }
    } else {
        fwrite($plik, "Błąd: Nie udało się pobrać danych dla klienta o ID $nr\n");
    }
    fclose($plik);
}

function usun_klienta($nr) {
    global $polaczenie;

    $zapytanie = "DELETE FROM klienci WHERE id=$nr;";
    mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");
}

function edytuj_klienta($nr = -1, $imie = '', $nazwisko = '', $kontakt = '', $email = '') {
    global $polaczenie;

    if ($nr != -1) {
        $zapytanie = "SELECT imie, nazwisko, kontakt, email FROM klienci WHERE id=$nr;";
        $rekord = mysqli_query($polaczenie, $zapytanie) or exit("Bład zapytania");

        $Klient = mysqli_fetch_row($rekord);
        $imie = $Klient[0];
        $nazwisko = $Klient[1];
        $kontakt = $Klient[2];
        $email = $Klient[3];
    } else {
        $imie = "";
        $nazwisko = "";
        $kontakt = "";
        $email = "";
    }
?>

<form method=Post action='' class='client-form'>
<table class='form-table'>
    <tr>
    <td>Imię</td><td colspan=2>
    <input type=text name='imie' value='<?=$imie?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Nazwisko</td><td colspan=2>
    <input type=text name='nazwisko' value='<?=$nazwisko?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Kontakt</td><td colspan=2>
    <input type=text name='kontakt' value='<?=$kontakt?>' class='input-field'></td>
    </tr>
    <tr>
    <td>Email</td><td colspan=2>
    <input type=text name='email' value='<?=$email?>' class='input-field'></td>
    </tr>
    <tr>
    <td colspan=3>
    <input type=submit name='przycisk[<?=$nr?>]' value='Zapisz' class='btn'></td>
    </tr>
</table>
</form>
<?php
}

function zapisz_klienta($nr) {
    global $polaczenie;

    $imie = isset($_POST['imie']) ? trim($_POST['imie']) : '';
    $nazwisko = isset($_POST['nazwisko']) ? trim($_POST['nazwisko']) : '';
    $kontakt = isset($_POST['kontakt']) ? trim($_POST['kontakt']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    $errors = [];

    if (empty($imie)) {
        $errors[] = "Pole 'Imię' jest wymagane.";
    }

    if (empty($nazwisko)) {
        $errors[] = "Pole 'Nazwisko' jest wymagane.";
    }

    if (empty($kontakt)) {
        $errors[] = "Pole 'Kontakt' jest wymagane.";
    } elseif (!preg_match("/^[0-9]{9}$/", $kontakt)) {
        $errors[] = "Podano niepoprawny numer kontaktowy. Numer musi mieć 9 cyfr.";
    }

    if (empty($email)) {
        $errors[] = "Pole 'Email' jest wymagane.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Podano niepoprawny adres email.";
    }

    if (!empty($errors)) {
        echo "<center><div class='error-messages' style='color: red;'>";
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
        echo "</div></center>";
        
        edytuj_klienta($nr, $imie, $nazwisko, $kontakt, $email);
        return; 
    }

    if ($nr != -1) {
        $rozkaz = "UPDATE klienci SET imie='$imie', nazwisko='$nazwisko', kontakt='$kontakt', email='$email' WHERE id=$nr;";
    } else {
        $rozkaz = "INSERT INTO klienci (imie, nazwisko, kontakt, email) VALUES ('$imie', '$nazwisko', '$kontakt', '$email');";
    }
    if (!mysqli_query($polaczenie, $rozkaz)) {
        exit("Błąd zapytania SQL: " . mysqli_error($polaczenie));
    }

    header("Location: Klienci.php");
    exit;
}

?>

<html>
<head>
<meta charset="utf-8">
<title>Obsługa Klienta</title>
<link rel="stylesheet" href="includes/style.css">
</head>

<center>

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
    case 'Edytuj': edytuj_klienta($nr); break;
    case 'Nowy Klient': edytuj_klienta(); break;
    case 'Zapisz': zapisz_klienta($nr); break;
    case 'Zapisz w pliku': zapisz_klienta_to_pliku($nr); break;
    case 'Usuń': usun_klienta($nr); break;
}

wypisz_Klienta($filter_field, $filter_value);
zamknij_polaczenia();
?>

</center>
</body>
</html>
