# Aplicatie multifunctionala Slim 4

Aceasta aplicatie foloseste [Slim 4](https://www.slimframework.com/) ca micro-framework si poate fi gazduita pe orice serviciu de tip shared-hosting care ofera PHP 7.4+ si SQLite. Tot codul sursa PHP se afla in directorul `public/`, iar baza de date `SQLite` este plasata in directorul `data/` din afara `public/`.

## Instalare
1. Clonati proiectul sau copiati fisierele pe server prin FTP.
2. In directorul radacina rulati `composer install` local pentru a genera folderul `vendor/` apoi incarcati si acesta pe server. Alternativ, puteti rula `composer install` direct pe server daca aveti acces la comanda.
3. Asigurati-va ca directorul `data/` are permisiuni de scriere de catre serverul web.
4. Accesati `public/index.php` din browser. La prima rulare se creeaza automat baza de date si parola implicita este `Euro2369!`.

## Functionalitati
- **Autentificare** – accesul la orice pagina necesita autentificare pe baza de parola. Parola implicita este criptata si poate fi modificata din sectiunea *Administrare*.
- **Panou administrare** – permite schimbarea parolei salvate in baza de date.
- **Poor-man cron** – la fiecare request se executa un script care poate rula sarcini programate.
- **Interfata dark mode** – foloseste TailwindCSS, Animate.css si Motion One pentru un design modern si animatii. Logica de pe client este gestionata cu Alpine.js, iar htmx se foloseste pentru eventuale cereri AJAX/SSE.

Aceasta structura minimalista permite extinderea aplicatiei prin adaugarea de noi rute in `public/index.php` si de noi functii in baza de date.
