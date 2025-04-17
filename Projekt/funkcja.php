<?php
    function otworz_polaczenie() {
        global $polaczenie;
        $serwer = "127.0.0.1";
        $uzytkownik = "root";
        $haslo = "";
        $baza = "warsztat_samochodowy";

        mysqli_report(MYSQLI_REPORT_OFF);
        $polaczenie = mysqli_connect($serwer, $uzytkownik, $haslo) or exit("Nieudane połaczenie z serwerem");

        if (!mysqli_select_db($polaczenie, $baza)) {
            if(mysqli_errno($polaczenie) == 1049) {
                utworz_baze();
                mysqli_select_db($polaczenie, $baza);
                utworz_tabele();
                wstaw_dane();
            }
            else echo ("Połączenie z bazą danych $baza nieudane");
        }
        mysqli_set_charset($polaczenie, "utf8");
    }

    function zamknij_polaczenia() {
        global $polaczenie;
        mysqli_close($polaczenie);
    }

    function utworz_baze() {
        $polaczenie = mysqli_connect("127.0.0.1", "root", "") or exit ("Nieudane połaczenie z serwerem");
        $baza = "warsztat_samochodowy";

        echo "Tworzenie bazę danych '$baza' ... <br>";
        mysqli_query($polaczenie, "CREATE DATABASE `$baza` DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci;") 
        or exit("Bład w zapyatniu tworzący bazę");
    }

    function utworz_tabele() {
        global $polaczenie;
    
        $rozkaz = "CREATE TABLE klienci (
            id INT NOT NULL AUTO_INCREMENT,
            imie VARCHAR(50),
            nazwisko VARCHAR(50),
            kontakt VARCHAR(100),
            email VARCHAR(100),
            PRIMARY KEY (id)
        )";
        mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
    
        $rozkaz = "CREATE TABLE samochody (
            id INT NOT NULL AUTO_INCREMENT,
            id_klienta INT NOT NULL,
            marka VARCHAR(50),
            model VARCHAR(50),
            rok INT,
            numer_rejestracyjny VARCHAR(20),
            PRIMARY KEY (id),
            FOREIGN KEY (id_klienta) REFERENCES klienci(id)
        )";
        mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
    
        $rozkaz = "CREATE TABLE mechanicy (
            id INT NOT NULL AUTO_INCREMENT,
            imie VARCHAR(50),
            nazwisko VARCHAR(50),
            specjalizacja VARCHAR(100),
            dni_robocze_od TIME,
            dni_robocze_do TIME,
            weekend_od TIME NULL,
            weekend_do TIME NULL,
            PRIMARY KEY (id)
        )";
        mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
    
        $rozkaz = "CREATE TABLE naprawy (
            id INT NOT NULL AUTO_INCREMENT,
            id_samochodu INT NOT NULL,
            id_mechanika INT NOT NULL,
            data_rozpoczecia DATE,
            data_zakonczenia DATE,
            koszt DECIMAL(10,2),
            status ENUM('w trakcie', 'zakonczona', 'oczekuje'),
            PRIMARY KEY (id),
            FOREIGN KEY (id_samochodu) REFERENCES samochody(id),
            FOREIGN KEY (id_mechanika) REFERENCES mechanicy(id)
        )";
        mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");

        $rozkaz = "CREATE TABLE uzytkownicy (
            id INT NOT NULL AUTO_INCREMENT,
            login VARCHAR(50) NOT NULL,
            haslo VARCHAR(255) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE (login)
        )";
        mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
    }
    
    function wstaw_dane() {
        global $polaczenie;
        mysqli_set_charset($polaczenie, "utf8");
    
        $rozkazy = array(
            "INSERT INTO klienci VALUES (NULL, 'Jan', 'Kowalski', '123-456-789', 'jan.kowalski@example.com');",
            "INSERT INTO klienci VALUES (NULL, 'Anna', 'Nowak', '987-654-321', 'anna.nowak@example.com');",
            "INSERT INTO klienci VALUES (NULL, 'Piotr', 'Wiśniewski', '654-321-987', 'piotr.wisniewski@example.com');",
            "INSERT INTO klienci VALUES (NULL, 'Katarzyna', 'Zielińska', '789-123-456', 'katarzyna.zielinska@example.com');",
            "INSERT INTO klienci VALUES (NULL, 'Tomasz', 'Kowalczyk', '321-654-987', 'tomasz.kowalczyk@example.com');",
            "INSERT INTO klienci VALUES (NULL, 'Joanna', 'Mazur', '456-789-123', 'joanna.mazur@example.com');"
        );
        foreach ($rozkazy as $rozkaz) {
            mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
        }
    
        $rozkazy = array(
            "INSERT INTO samochody VALUES (NULL, 1, 'Toyota', 'Corolla', 2015, 'KR12345');",
            "INSERT INTO samochody VALUES (NULL, 2, 'Ford', 'Focus', 2018, 'WA98765');",
            "INSERT INTO samochody VALUES (NULL, 3, 'Volkswagen', 'Golf', 2020, 'GD56789');",
            "INSERT INTO samochody VALUES (NULL, 4, 'Honda', 'Civic', 2017, 'PO87654');",
            "INSERT INTO samochody VALUES (NULL, 5, 'BMW', '3 Series', 2019, 'WX34567');",
            "INSERT INTO samochody VALUES (NULL, 6, 'Audi', 'A4', 2016, 'LU98765');"
        );
        foreach ($rozkazy as $rozkaz) {
            mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
        }
    
        $rozkazy = array(
            "INSERT INTO mechanicy VALUES (NULL, 'Piotr', 'Zieliński', 'Elektryka', '10:00', '18:00', NULL, NULL);",
            "INSERT INTO mechanicy VALUES (NULL, 'Tomasz', 'Wiśniewski', 'Mechanika ogólna', '08:00', '16:00', NULL, NULL);",
            "INSERT INTO mechanicy VALUES (NULL, 'Jan', 'Kowalczyk', 'Wulkanizacja', '09:00', '17:00', '10:00', '14:00');",
            "INSERT INTO mechanicy VALUES (NULL, 'Anna', 'Mazur', 'Blacharstwo', '07:00', '15:00', NULL, NULL);",
            "INSERT INTO mechanicy VALUES (NULL, 'Krzysztof', 'Nowak', 'Lakiernictwo', '11:00', '19:00', NULL, NULL);",
            "INSERT INTO mechanicy VALUES (NULL, 'Joanna', 'Wiśniewska', 'Diagnostyka', '12:00', '20:00', NULL, NULL);"
        );
        foreach ($rozkazy as $rozkaz) {
            mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
        }
    
        $rozkazy = array(
            "INSERT INTO naprawy VALUES (NULL, 1, 1, '2025-01-01', '2025-01-10', 1500.00, 'zakonczona');",
            "INSERT INTO naprawy VALUES (NULL, 2, 2, '2025-01-05', NULL, 0.00, 'w trakcie');",
            "INSERT INTO naprawy VALUES (NULL, 3, 3, '2025-01-10', NULL, 500.00, 'Oczekuje');",
            "INSERT INTO naprawy VALUES (NULL, 4, 4, '2025-01-15', NULL, 0.00, 'Oczekuje');",
            "INSERT INTO naprawy VALUES (NULL, 5, 5, '2024-12-20', '2024-12-25', 800.00, 'zakonczona');",
            "INSERT INTO naprawy VALUES (NULL, 6, 6, '2025-01-12', '2025-01-18', 2000.00, 'zakonczona');"
        );
        foreach ($rozkazy as $rozkaz) {
            mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
        }

        $haslo_admin1 = password_hash("admin123", PASSWORD_DEFAULT);
        $haslo_admin2 = password_hash("admin1233", PASSWORD_DEFAULT);

        $rozkazy = array(
            "INSERT INTO uzytkownicy (login, haslo) VALUES ('admin1', '$haslo_admin1');",
            "INSERT INTO uzytkownicy (login, haslo) VALUES ('admin2', '$haslo_admin2');"
        );
        foreach ($rozkazy as $rozkaz) {
            mysqli_query($polaczenie, $rozkaz) or exit("Błąd w zapytaniu: $rozkaz");
        }
    }
?>