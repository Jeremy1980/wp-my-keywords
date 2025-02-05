# wp-my-keywords
Strating point for any Wordpress pluging

- Po porawnej aktywacji tworzona jest tabela [wordpress_prefix]mykeywords.
- Na uwagę zasługuje zdefiniowanie klucza głównego jako kolumna id oraz unikalnego indeksu z kolumn shortcode oraz keyword.
   Dzięki temu możliwe stało się INSERT INTO ... ON DUPLICATE KEY UPDATE ...
   Rozwiązanie to zapewnia unikalność dodawanych treści oraz poprawną aktualizację wiersz w/w tabeli.
- Wtyczka instaluje się w WPA > Ustawienia > Złota myśl.
- W polu **Twoja nazwa** ustawiasz tekst zastępczy (ang. shortcode) którego użycie spowoduje wyświetlenie treści użytej w polu **Twoja zawartość**.
