# Verschillen tussen de eisen, mijn ontwerp en wat ik gebouwd heb

Hier staat wat er anders is dan in mijn wireframes of in het programma van eisen, en waarom ik dat zo gedaan heb.

## Verschillen met mijn wireframes

| # | In mijn wireframe | Wat ik gebouwd heb | Waarom |
|---|---|---|---|
| V1 | Blauwe en groene knoppen, "Harbor Stage" linksboven | Hetzelfde als mijn wireframes: blauwe, groene, rode en grijze knoppen, een lichte achtergrond en een donkere balk voor medewerkers | De kleuren uit de bijlage (#18181B, #DB2777, #F59E0B, #FAFAFA) zijn volgens de opdrachtgever een richting. Er staat ook: "een passend alternatief mag ook". Ik heb de kleuren van mijn eigen ontwerp gekozen. Oranje (#F59E0B) gebruik ik wel voor de waarschuwing bij het scannen. |
| V2 | Detailpagina met een grote afbeelding (vak met een kruis) | Geen afbeelding | Afbeeldingen uploaden staat niet in de eisen voor de eerste versie. Uploads geven ook extra beveiligingsrisico's. |
| V3 | Detailpagina met alleen "Beschikbare plaatsen" | Ook "X van Y", de verkoopperiode, het programma en een statuslabel (kleur én tekst) | Het programma van eisen vraagt om een programma, resterende plaatsen en een verkoopperiode. Een capaciteitsbalk (uit de bijlage) heb ik weggelaten om het simpel te houden. Het aantal vrije plaatsen staat als getal op de kaart en op de detailpagina. |
| V4 | *Mijn reserveringen* heeft een kolom "Ticketcode" met één code | De knop "Details" opent een pagina met een kaartje per ticket | Reserveer je meer tickets, dan heeft elk ticket een eigen code. Elke code kan namelijk maar één keer gebruikt worden. |
| V5 | *Mijn reserveringen* heeft de status "In behandeling" | Alleen "Bevestigd" en "Geannuleerd" | In de eerste versie zit geen betaling. Een reservering is dus meteen bevestigd. |
| V6 | *Evenementbeheer* heeft de knoppen "Bewerken", "Annuleren" en "Verwijderen" | "Bewerken", "Reserveringen" en "Verwijderen" (alleen als er nog geen tickets verkocht zijn). Annuleren doe je met de status "Geannuleerd" in het formulier | Zo kan een evenement met reserveringen niet per ongeluk verwijderd worden. Dan zouden de tickets van mensen weg zijn. |
| V7 | Formulier *Nieuw evenement* zonder verkoopperiode, met losse velden voor datum en tijd | Velden "Start verkoop", "Einde verkoop", "Status" en "Programma" erbij. Datum en tijd zitten in één veld | Het programma van eisen vraagt om capaciteit **en verkoopperiode**, en om een **programma** op de detailpagina. Eén veld voor datum en tijd is makkelijker te checken. |
| V8 | Geen scherm om reserveringen te filteren en geen scanscherm | Extra schermen *Reserveringen* en *Tickets scannen* voor medewerkers | Allebei staan ze in de functies voor medewerkers en in de bijlage ("snelle scanweergave"). |

## Verschillen met het programma van eisen

| # | Eis | Wat ik gebouwd heb | Uitleg |
|---|---|---|---|
| E1 | "Ticketcode controleren aan de deur" | De code intypen (een handscanner die als toetsenbord werkt, kan ook) | Een QR-code scannen met de camera heeft een extra bibliotheek nodig. Dat is niet nodig voor de eerste versie. De codes zijn makkelijk over te typen (geen 0/O en 1/I). |
| E2 | Accounts voor medewerkers | Medewerkers kunnen zich niet zelf registreren. Hun account staat in de database (seed) | Zo kan een bezoeker zichzelf nooit medewerker maken. Een scherm om accounts te beheren kan later nog. |
| E3 | Maximum per reservering | Maximaal 10 tickets per reservering (aan te passen in `config.php`) | Dit stond niet in de eisen, maar zo kan één persoon niet de hele zaal in één keer reserveren. |
| E4 | – | Bezoekers kunnen annuleren tot het evenement begint, zolang er nog geen ticket gescand is | Dat vond ik logisch: een gescand ticket is al gebruikt. |

## Verschillen met mijn planning

| # | In mijn planning | Wat ik gedaan heb | Waarom |
|---|---|---|---|
| P1 | FE-07 (filteren), FE-08 (ticket scannen) en RV-05 hebben geen bouwtaak. De takenlijst stopt bij T-14 | Toch gebouwd, in 3 commits op de branch `feature/medewerker` | Ze staan wel in de eisen en horen bij de eerste versie, maar ontbraken in mijn eerste planning. |
| P2 | FE-02 heeft de tekst "account aanmaken", maar de testuitkomst gaat over de detailpagina | FE-02 behandel ik als de detailpagina (net als taak T-10) | Dat was een fout in mijn planning. Account aanmaken valt onder FE-03. |
| P3 | T-08 heet "Rollen admin bouwen" met het resultaat "zoeken op datum of categorie werkt" | T-08 is het bouwen van de rollen (bezoeker en medewerker) | Het resultaat bij T-08 klopte niet; dat hoort bij T-09. |
| P4 | Er staat een "FE 09" bij T-13 (capaciteit) | Capaciteit hoort bij RV-04 | FE-09 bestaat niet in mijn lijst met eisen. |

## Wat ik bewust niet gebouwd heb

- Online betalen en een koppeling met een betaalprovider.
- Koppelingen met andere websites (API's).
- Tickets mailen.
- Wachtwoord vergeten en account aanpassen.
- Afbeeldingen uploaden bij evenementen.
