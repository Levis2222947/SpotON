# SpotOn – Local Event Ticketing

Dit is mijn ticketsite voor **Poppodium Harbor Stage**. Bezoekers kunnen evenementen zoeken en tickets reserveren. Medewerkers kunnen evenementen beheren en bij de deur tickets scannen met een unieke code.

- **Gemaakt met:** PHP 8, MySQL (via PDO), mijn eigen CSS en een paar korte stukjes JavaScript in de HTML (bijv. "Weet u het zeker?"). Ik gebruik geen framework en geen klassen, alleen gewone functies.
- **Definitieve versie:** branch `main`, tag `v1.0.0`.

## Zo zet je het op je eigen computer (XAMPP)

1. Zet de map in `C:\xampp\htdocs\`, bijvoorbeeld `htdocs\SpotON`.
2. Start Apache en MySQL in het XAMPP Control Panel.
3. Maak de database en zet de testdata erin:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql
   C:\xampp\mysql\bin\mysql.exe -u root spoton < database/seed.sql
   ```
   Dit kan ook via phpMyAdmin: eerst `schema.sql` importeren, daarna `seed.sql`.
4. Wil je andere databasegegevens? Kopieer `app/config/config.example.php` naar `app/config/config.local.php` en pas ze daar aan. Voor XAMPP hoeft dat niet, `root` zonder wachtwoord werkt meteen.
5. Ga naar `http://localhost/SpotON/` (of het pad waar jij de map hebt neergezet, bijv. `http://localhost/fotoshooty/SpotON/`).

## Zo zet je het online (Plesk)

1. Maak in Plesk een database `spoton` met een gebruiker.
2. Importeer in phpMyAdmin eerst `database/schema.sql` en daarna `database/seed.sql`. Haal in `schema.sql` de regels `CREATE DATABASE` en `USE` weg als je geen database mag aanmaken.
3. Upload alle bestanden naar `httpdocs/`.
4. Zet bij **Hosting-instellingen** de documentroot op `httpdocs/public`. Lukt dat niet, dan stuurt de `.htaccess` in de hoofdmap alles zelf door naar `public/`.
5. Maak `app/config/config.local.php` aan met de databasegegevens van Plesk.

## Testaccounts

| Rol | E-mail | Wachtwoord |
|---|---|---|
| Ticketmedewerker | `medewerker@spoton.test` | `Welkom123!` |
| Bezoeker | `bezoeker@spoton.test` | `Welkom123!` |

Maak je zelf een account via *Registreren*? Dan ben je altijd een **bezoeker**.

## Hoe mijn mappen in elkaar zitten

```
public/
  index.php           hier begint elke pagina: een switch kiest welke functie er draait
  assets/             CSS
app/
  bootstrap.php       laadt de instellingen en al mijn functies, en start de sessie
  helpers.php         kleine hulpfuncties, zoals e(), url(), redirect(), inputInt() en formatDate()
  config/             instellingen (database, max. aantal tickets)
  Core/               de basis: database.php, view.php, auth.php (inloggen en rollen) en csrf.php
  Models/             alle SQL-query's, één bestand per tabel (Event.php, Reservation.php, ...)
  Controllers/        één functie per pagina of formulier (bijv. showEventList(), handleReserve())
    Staff/            de functies voor medewerkers
  views/              de HTML van elke pagina, per onderdeel een map
database/             schema.sql (de tabellen) en seed.sql (testdata)
docs/                 mijn verantwoording: eisen, verschillen, beveiliging en testplan
storage/logs/         logbestand met fouten
```

### Wat er gebeurt bij één pagina (voorbeeld: `index.php?page=event&id=1`)

1. `public/index.php` laadt `app/bootstrap.php` en ziet `page=event`.
2. De `switch` roept de controllerfunctie `showEventDetail()` aan.
3. Die vraagt het model om het evenement: `findVisibleEvent(1)`. Dat is een SQL-query met een `?`.
4. Daarna laat `view('events/show', [...])` de HTML zien met de gegevens.

## Documentatie

- [docs/traceability.md](docs/traceability.md): bij welke eis, welk scherm en welke taak elke functie hoort
- [docs/verschillen.md](docs/verschillen.md): wat er anders is dan in de eisen en mijn ontwerp, en waarom
- [docs/beveiliging.md](docs/beveiliging.md): hoe ik de site beveiligd heb
- [docs/testplan.md](docs/testplan.md): mijn tests en het script voor de demovideo
