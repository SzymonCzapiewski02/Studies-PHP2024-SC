<?php
    include('funkcja.php');
    session_start();

    otworz_polaczenie();
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = $_POST['login'];
        $haslo = $_POST['haslo'];
        global $polaczenie;
    
        $zapytanie = "SELECT * FROM uzytkownicy WHERE login = '$login'";
        $wynik = mysqli_query($polaczenie, $zapytanie);
    
        if ($wynik) {
            $uzytkownik = mysqli_fetch_row($wynik);
    
            if ($uzytkownik && password_verify($haslo, $uzytkownik[2])) {
                $_SESSION['zalogowany'] = true;
                $_SESSION['login'] = $login;
                header("Location: index.php");
                exit();
            } else {
                $error = "Błędny login lub hasło!";
            }
        } else {
            $error = "Błąd zapytania do bazy danych!";
        }
    }
    zamknij_polaczenia();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="includes/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
    </style>
</head>
<body>
    <h1 class="h1Style">Logowanie</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form class="logowanie" method="POST" action="">
        <label class="napisz" for="login">Login</label>
        <input type="text" class="pole" id="login" name="login" required>

        <label class="napisz" for="haslo">Hasło</label>
        <input type="password" class="pole" id="haslo" name="haslo" required>

        <input type="submit" value="Zaloguj się" class="btn">
    </form>

    <div class="footer">&copy; 2025 Warsztat. Wszelkie prawa zastrzeżone.</div>
</body>
</html>