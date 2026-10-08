# Traceability: eis ↔ ontwerp ↔ planning ↔ code

Hier zie je bij elke functie bij welke eis, welk scherm uit mijn ontwerp en welke taak uit mijn planning hij hoort, en in welke commit ik hem gebouwd heb.
De ID's (RV, FE, TE en T) komen uit mijn document **Planning & voortgang (KT1-W1, versie 7 september)**.
Het medewerkersdeel heb ik gebouwd op de branch `feature/medewerker` en via pull request #1 in `main` gemerged.

## Functionele eisen

| Eis | Wat het doet | Ontwerp (wireframe) | Taak in planning | Commit | Code |
|---|---|---|---|---|---|
| FE-01 | Evenementen zoeken op datum en categorie | *Home – Aankomende evenementen* (zoekbalk) | T-09 | `main` – 84073eb | `showEventList()`, `searchEvents()`, `views/events/index.php` |
| FE-02 | Detailpagina met programma, locatie en resterende plaatsen | *Detailpagina evenement* | T-10 | `main` – 84073eb | `showEventDetail()`, `remainingSeats()`, `programLines()`, `views/events/show.php` |
| FE-03 | Account maken en inloggen | *Account aanmaken*, *Inloggen* | T-07 | `main` – 8d530ce | `handleRegister()`, `handleLogin()`, `createVisitor()`, `checkLogin()` |
| FE-04 | Eén of meer tickets reserveren | *Tickets reserveren* | T-12 | `main` – 54c4884 | `showReserveForm()`, `handleReserve()`, `createReservation()`, `createTickets()` |
| FE-05 | Eigen reserveringen bekijken en annuleren | *Mijn reserveringen* | T-14 | `main` – 54c4884 | `showMyReservations()`, `showReservation()`, `handleCancelReservation()` |
| FE-06 | Evenementen aanmaken, wijzigen, verwijderen of annuleren (met capaciteit en verkoopperiode) | *Evenementbeheer*, *Nieuw evenement* | T-11 | `feature/medewerker` – c129c83 (PR #1) | `Controllers/Staff/EventController.php`, `checkEventForm()` |
| FE-07 | Reserveringen filteren op status en evenement | geen wireframe (zie verschillen V8) | geen taak in mijn eerste planning (zie verschillen P1) | `feature/medewerker` – 28becf7 (PR #1) | `showStaffReservations()`, `searchReservations()` |
| FE-08 | Ticketcode checken en als gebruikt registreren | geen wireframe (zie verschillen V8) | geen taak in mijn eerste planning (zie verschillen P1) | `feature/medewerker` – a158534 (PR #1) | `showScanPage()`, `handleScan()`, `useTicket()` |

## Randvoorwaarden

| Eis | Wat het betekent | Taak in planning | Commit | Code |
|---|---|---|---|---|
| RV-01 | Werkt op telefoon en computer | T-05 (responsive wireframes) | `main` – f8b587e | `public/assets/css/style.css` (telefoonweergave onder 600px) |
| RV-02 | PHP 8+ met MySQL/MariaDB | T-06 | `main` – 61d5768 | `Core/database.php`, `database/schema.sql` |
| RV-03 | Iedere gebruiker mag alleen wat bij zijn rol hoort | T-08 | `main` – f8b587e | `Core/auth.php`, `public/index.php` (`requireStaff()`, `requireVisitor()`) |
| RV-04 | Nooit meer tickets dan de capaciteit (en geannuleerde tickets komen weer vrij) | T-13 | `main` – 11adf9b, 54c4884 | `createReservation()` (transactie + `FOR UPDATE`), `checkEventForm()` (capaciteit ≥ verkocht), `cancelReservation()` |
| RV-05 | Een gebruikte ticketcode kan niet opnieuw gebruikt worden | geen taak in mijn eerste planning (zie verschillen P1) | `feature/medewerker` – a158534 | `useTicket()` (`FOR UPDATE`, alleen `valid` wordt `used`) |

## Technische eisen

| Eis | Wat het betekent | Taak in planning | Code |
|---|---|---|---|
| TE-01 | PHP 8+ en MySQL/MariaDB | T-06 | `Core/database.php` |
| TE-02 | Wachtwoorden veilig opslaan | T-07 | `createVisitor()` (`password_hash`), `checkLogin()` (`password_verify`) |
| TE-03 | Veilige databasequeries en invoer controleren | T-06, T-07 en verder | Alle queries met `?` in `Models/*`; controle per veld in de controllers; uitvoer via `e()` |
| TE-04 | Autorisatie op basis van de rol | T-08 | `requireStaff()`, `requireVisitor()` in `public/index.php` |

## Design-wensen (bijlage)

| Wens | Uitwerking |
|---|---|
| Kleuren #18181B, #DB2777, #F59E0B, #FAFAFA (richting) | Ik heb de kleuren van mijn eigen wireframes gebruikt (zie verschillen V1). Oranje gebruik ik voor de waarschuwing bij het scannen |
| Affiche-achtig evenementenoverzicht | Een raster met evenementkaarten (`partials/event-card.php`), zoals in mijn wireframe |
| Snelle scanweergave voor personeel | `staff/scan`: de cursor staat meteen in het codeveld, een groot invoerveld, Enter = checken |
| Evenementkaarten, ticketkaart, opvallende scanstatus | `partials/event-card.php`, `.ticket` in `reservations/show.php`, `.scan-status` (capaciteitsbalk weggelaten, zie verschillen V3) |
| Status met kleur én tekst | De functie `badge()` in `helpers.php` (gekleurd label met tekst), en de scanstatus met kleur + tekst |
| Formulieren met duidelijke labels en foutmeldingen | Elk veld heeft een `<label>`. Fouten staan onder het veld (`fieldError()`) |

## Ontwerptaken uit mijn planning

| Taak | Resultaat |
|---|---|
| T-01 | Lijst met eisen (zie de tabellen hierboven) |
| T-02, T-03 | Databaseontwerp: `database/schema.sql` (tabellen users, categories, events, reservations, tickets) |
| T-04, T-05 | Wireframes voor bezoeker en medewerker |
| T-06 | Basis van de applicatie: `public/index.php`, `app/bootstrap.php`, `Core/` |
