# Beveiliging

Hier staat welke risico's ik heb bedacht en wat ik ertegen gedaan heb.

| Risico | Wat ik gedaan heb | Waar in de code |
|---|---|---|
| Wachtwoorden lekken uit | Ik sla wachtwoorden op met `password_hash()`. Er staat nooit een echt wachtwoord in de database. Bij inloggen check ik met `password_verify()`. | `Models/User.php` (`createVisitor`, `checkLogin`) |
| Uitzoeken welke e-mailadressen een account hebben | Bij een fout geef ik altijd dezelfde melding: "Het e-mailadres of wachtwoord is onjuist". | `handleLogin()` |
| SQL-injectie | Al mijn query's zijn prepared statements met een `?`. Ik plak nooit invoer in de SQL. | `Core/database.php`, `Models/*` |
| XSS (scripts in invoer) | Alles wat ik laat zien gaat door `e()` (`htmlspecialchars`). Getest met `<b>` en `<script>` in een naam: het komt er gewoon als tekst te staan. | `helpers.php`, alle views |
| CSRF (een andere site verstuurt een formulier namens jou) | Elk formulier heeft een geheime code (`csrfField()`). In `index.php` check ik die bij elk formulier (`checkCsrf()`). Klopt hij niet, dan krijg je pagina 400 "Formulier verlopen". Uitloggen, annuleren en verwijderen kan alleen via een formulier. | `Core/csrf.php`, `public/index.php` |
| Pagina's openen waar je niet bij mag | In `index.php` check ik de rol: pagina's die met `staff/` beginnen → `requireStaff()`, reserveren → `requireVisitor()`. Niet ingelogd → inlogscherm. Verkeerde rol → 403. | `Core/auth.php`, `public/index.php` |
| Reserveringen van iemand anders bekijken of annuleren | Ik zoek reserveringen altijd op met `WHERE r.id = ? AND r.user_id = ?`. Verander je het nummer in de URL, dan krijg je 404. | `findReservationForUser()`, `cancelReservation()` |
| Jezelf medewerker maken | Bij registreren zet ik de rol altijd op `visitor`. De rol lees ik nooit uit het formulier. | `createVisitor()` |
| Sessie overnemen | Het sessiecookie is `HttpOnly` (JavaScript kan er niet bij) en `SameSite=Lax`. Na in- en uitloggen maak ik een nieuw sessie-id. | `app/bootstrap.php`, `Core/auth.php` |
| Te veel tickets als twee mensen tegelijk reserveren | Ik reserveer in een transactie met `SELECT … FOR UPDATE`. Het evenement staat dan even op slot, en pas daarna tel ik de vrije plaatsen. | `createReservation()` |
| Een ticket twee keer gebruiken | Bij het scannen zet ik het ticket op slot (`FOR UPDATE`). Alleen een ticket met status `valid` wordt `used`. | `useTicket()` |
| Ticketcodes raden | De codes maak ik met `random_int()`. Er zijn 32^8 (ongeveer 1 biljoen) mogelijke codes, en elke code is uniek in de database. | `generateTicketCode()` |
| Bestanden direct openen via de browser | Alleen `public/` is te bereiken. In `app/`, `database/` en `storage/` staat een `.htaccess` met `Require all denied`. | `.htaccess` |
| Foutmeldingen met geheime info | `display_errors` staat uit. Fouten gaan naar `storage/logs/error.log` en de bezoeker ziet een nette pagina. | `app/bootstrap.php` |
| Wachtwoorden op GitHub | Mijn databasegegevens staan in `config.local.php`, en dat bestand staat in `.gitignore`. | `.gitignore` |
