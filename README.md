# SpotOn – Local Event Ticketing

Compact ticketplatform voor **Poppodium Harbor Stage**: bezoekers zoeken evenementen en reserveren tickets, ticketmedewerkers beheren evenementen en controleren tickets aan de deur met een unieke code.

- **Techniek:** PHP 8+ (zonder framework), MySQL/MariaDB via PDO, eigen CSS (responsive), een klein beetje JavaScript.
- **Definitieve versie:** branch `main`, tag `v1.0.0`.

## Installatie (lokaal met XAMPP)

1. Zet de map in `C:\xampp\htdocs\` (bijv. `htdocs\SpotON`).
2. Start Apache en MySQL in het XAMPP Control Panel.
3. Maak de database aan en vul hem met testdata:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql
   C:\xampp\mysql\bin\mysql.exe -u root spoton < database/seed.sql
   ```
   (of importeer beide bestanden via phpMyAdmin).
4. Kopieer `app/config/config.example.php` naar `app/config/config.local.php` en pas de databasegegevens aan (voor XAMPP werkt de standaard `root` zonder wachtwoord meteen).
5. Open `http://localhost/SpotON/`.

## Installatie op Plesk

1. Maak in Plesk een database `spoton` met een gebruiker aan.
2. Importeer `database/schema.sql` en daarna `database/seed.sql` via phpMyAdmin (haal in `schema.sql` de regels `CREATE DATABASE` en `USE` weg als je geen rechten hebt om databases aan te maken).
3. Upload alle bestanden naar `httpdocs/`.
4. Zet bij **Hosting-instellingen** de documentroot op `httpdocs/public` (aanbevolen). Lukt dat niet, dan stuurt de `.htaccess` in de hoofdmap alles door naar `public/`.
5. Maak `app/config/config.local.php` aan met de databasegegevens van Plesk.

## Testaccounts

| Rol | E-mail | Wachtwoord |
|---|---|---|
| Ticketmedewerker | `medewerker@spoton.test` | `Welkom123!` |
| Bezoeker | `bezoeker@spoton.test` | `Welkom123!` |

Nieuwe accounts via *Registreren* krijgen altijd de rol **bezoeker**.

## Mappenstructuur

```
app/
  Controllers/        verwerken een verzoek (één controller per onderdeel)
    Staff/            controllers die alleen medewerkers mogen gebruiken
  Core/               herbruikbare basis: router, database, sessie/auth, CSRF, validatie, views
  Models/             alle databasequery's (User, Event, Reservation, Ticket)
  views/              HTML-templates, per onderdeel een map
  config/             instellingen
  bootstrap.php       laadt config, autoloader, sessie en foutafhandeling
  routes.php          overzicht van alle routes
  helpers.php         kleine hulpfuncties (e(), url(), datums)
database/             schema.sql en seed.sql
docs/                 verantwoording: eisen ↔ ontwerp ↔ planning, verschillen, testplan
public/               documentroot: index.php (front controller) en assets
storage/logs/         foutlogboek
```

## Documentatie

- [docs/traceability.md](docs/traceability.md) – welke functie hoort bij welke eis, welk scherm en welke planningstaak
- [docs/verschillen.md](docs/verschillen.md) – verschillen tussen eisen, ontwerp en product
- [docs/beveiliging.md](docs/beveiliging.md) – genomen beveiligingsmaatregelen
- [docs/testplan.md](docs/testplan.md) – handmatige tests en demoscript
