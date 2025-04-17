<?php
session_start();

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    header("Location: logowanie.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona głowna</title>
    <link rel="stylesheet" href="includes/style.css">
    <style>
        body {
            background: url('foto/foto.png') no-repeat center center fixed;
            background-size: cover;
        }
        .indexcss {
            margin-top: 0; /* Usunięcie zbędnego marginesu */
            padding-top: 60px; /* Dodanie odstępu od góry */
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="indexcss">
    <h1 class="h1Style">Witaj w warsztacie samochodowym!</h1>
    <h2 class="h1Style">Wybierz jedną z opcji w menu.</h2>
    </div>
</body>
</html>
