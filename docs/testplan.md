# Testplan en demoscript

Dit zijn de tests die ik met de hand gedaan heb.
Testaccounts: `bezoeker@spoton.test` en `medewerker@spoton.test`, wachtwoord `Welkom123!`.
Voor het testen zet ik de testdata terug met `schema.sql` en `seed.sql`.

## Mijn tests

| # | Test | Wat ik doe | Wat er moet gebeuren | Resultaat |
|---|---|---|---|---|
| 1 | Zoeken op categorie | Home → categorie "Workshop" → Zoeken | Alleen *Workshop Songwriting* | ✅ |
| 2 | Zoeken zonder resultaat | Kies een datum zonder evenementen | Melding "Geen evenementen gevonden" + knop "Wis filters" | ✅ |
| 3 | Detailpagina | Klik op *Zomer Jazz Festival* | Programma, locatie, beschikbare plaatsen, verkoopperiode, status | ✅ |
| 4 | Concept niet zichtbaar | Open `?page=event&id=6` | 404 "Dit evenement bestaat niet of is niet meer zichtbaar" | ✅ |
| 5 | Registreren met fouten | Leeg formulier / wachtwoorden verschillend / bestaand e-mailadres | Foutmelding onder het juiste veld | ✅ |
| 6 | Inloggen met fout wachtwoord | Verkeerd wachtwoord | "Het e-mailadres of wachtwoord is onjuist." | ✅ |
| 7 | Reserveren als gast | Niet ingelogd → Reserveren | Naar inlogscherm met melding "Log eerst in" | ✅ |
| 8 | Tickets reserveren | 2 tickets voor *Theater: De Vuurtoren* | Succesmelding, 2 ticketkaarten met unieke codes | ✅ |
| 9 | Meer dan maximum | 999 tickets (via aangepast formulier) | "Kies een aantal tussen 1 en 10." | ✅ |
| 10 | Uitverkocht | *Workshop Songwriting* (4/4) | Status "Uitverkocht", reserveren niet mogelijk | ✅ |
| 11 | Capaciteit onder druk | 20 reserveringen tegelijk voor een evenement met nog 4 plaatsen | Precies 4 tickets uitgegeven | ✅ |
| 12 | Annuleren | Mijn reserveringen → Annuleren | Status "Geannuleerd", plaatsen weer vrij op de detailpagina | ✅ |
| 13 | Annuleren na gebruik | Reservering met een gescand ticket annuleren | Foutmelding, niet geannuleerd | ✅ |
| 14 | Reservering van ander | `?page=reservation&id=` van een andere gebruiker | 404 | ✅ |
| 15 | Bezoeker naar medewerkerpagina | `?page=staff/events` als bezoeker | 403 "Geen toegang" | ✅ |
| 16 | Medewerker reserveert | `?page=reserve&event=1` als medewerker | 403 "Alleen bezoekers…" | ✅ |
| 17 | Evenement aanmaken met fouten | Leeg formulier, verkoop-einde vóór start, categorie 99 | Foutmelding per veld | ✅ |
| 18 | Capaciteit verlagen onder verkocht | Workshop capaciteit → 2 | "Er zijn al 4 tickets verkocht…" | ✅ |
| 19 | Evenement met reserveringen verwijderen | Verwijderen | Foutmelding, advies status "Geannuleerd" | ✅ |
| 20 | XSS | Evenementnaam `<script>alert(1)</script>` | Tekst wordt letterlijk getoond, geen pop-up | ✅ |
| 21 | Formulier zonder CSRF-token | POST zonder `csrf_token` | Pagina 400 "Formulier verlopen" | ✅ |
| 22 | Scan geldig | Scan `SO-JAZZ-0001` | Groen "Geldig – toegang" | ✅ |
| 23 | Scan tweede keer | Zelfde code opnieuw | Rood "Al gebruikt" met tijdstip | ✅ |
| 24 | Scan geannuleerd ticket | `SO-COMD-0001` | Rood "Geannuleerd" | ✅ |
| 25 | Scan verkeerd evenement | Evenement *Jazz* gekozen, code `SO-SONG-0001` | Oranje "Ander evenement", ticket niet gebruikt | ✅ |
| 26 | Scan onbekende code | `ABC` | Grijs "Onbekende code" | ✅ |
| 27 | Scan met spaties/kleine letters | `so jazz 0002` | Wordt herkend als `SO-JAZZ-0002` | ✅ |
| 28 | Responsive | Pagina's op 375px, 768px en 1280px breed | Kaarten staan onder elkaar, menu loopt door op een nieuwe regel, tabellen zijn horizontaal te scrollen | ✅ |

## Script voor mijn demovideo (max. 3 minuten)

1. **(0:00) Bezoeker** – Home: overzicht met evenementkaarten, filter op categorie "Concert", lege zoekopdracht → melding.
2. **(0:25)** Detailpagina *Zomer Jazz Festival*: programma, locatie, beschikbare plaatsen.
3. **(0:40)** Registreren met een fout (wachtwoorden verschillend) → foutmelding → goed invullen.
4. **(1:00)** 2 tickets reserveren → ticketkaarten met unieke codes. Toon dat het aantal beschikbare plaatsen is bijgewerkt.
5. **(1:15)** *Workshop Songwriting* is uitverkocht → reserveren niet mogelijk.
6. **(1:25)** Mijn reserveringen → een reservering annuleren → plaatsen weer vrij.
7. **(1:40)** Probeer `?page=staff/events` als bezoeker → 403.
8. **(1:50) Medewerker** – Inloggen → Evenementbeheer → nieuw evenement met fout (verkoop-einde na aanvang) → corrigeren → opslaan.
9. **(2:15)** Reserveringen filteren op evenement en status.
10. **(2:30)** Scannen: kies het evenement, scan een code van stap 4 → groen. Scan dezelfde code opnieuw → rood "Al gebruikt".
11. **(2:50)** Telefoonweergave (DevTools) van home en scanpagina.
