-- SpotOn: testdata (verzonnen, geen echte personen)
-- Wachtwoord van beide accounts: Welkom123!
-- De datums reken ik uit vanaf NOW(), zodat de evenementen altijd in de toekomst liggen.

INSERT INTO users (id, name, email, password_hash, role) VALUES
(1, 'E. Jansen',     'medewerker@spoton.test', '$2y$10$o1yUUTwEC.PwXQiLeK7aWe6agwdHlqJ176AApnISA07BQSWn0f2FO', 'staff'),
(2, 'Sam Bezoeker',  'bezoeker@spoton.test',   '$2y$10$o1yUUTwEC.PwXQiLeK7aWe6agwdHlqJ176AApnISA07BQSWn0f2FO', 'visitor');

INSERT INTO categories (id, name) VALUES
(1, 'Concert'),
(2, 'Workshop'),
(3, 'Comedy'),
(4, 'Theater');

INSERT INTO events (id, title, description, program, location, category_id, starts_at, capacity, sale_starts_at, sale_ends_at, status) VALUES
(1, 'Zomer Jazz Festival',
 'Een avond vol swingende jazz met drie bands uit de regio. Neem plaats in de grote zaal en geniet van een drankje aan de bar.',
 '19:00 Deuren open\n19:30 Harbor Big Band\n20:45 Pauze\n21:15 Lisa Moreno Quartet\n22:30 Jamsessie',
 'Grote zaal', 1, DATE_ADD(DATE(NOW()), INTERVAL 14 DAY) + INTERVAL 19 HOUR, 150,
 DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_ADD(DATE(NOW()), INTERVAL 14 DAY) + INTERVAL 18 HOUR, 'published'),

(2, 'Stand-up Comedy Nacht',
 'Vier comedians testen hun nieuwste materiaal. Lachen gegarandeerd, maar niet geschikt voor kinderen.',
 '20:00 Deuren open\n20:30 Opener\n21:00 Drie headliners\n22:30 Einde',
 'Kleine zaal', 3, DATE_ADD(DATE(NOW()), INTERVAL 5 DAY) + INTERVAL 20 HOUR, 60,
 DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_ADD(DATE(NOW()), INTERVAL 5 DAY) + INTERVAL 19 HOUR, 'published'),

(3, 'Workshop Songwriting',
 'Leer in één middag hoe je een eigen nummer schrijft. Voor beginners; neem een notitieboek mee.',
 '13:00 Ontvangst\n13:30 Theorie: opbouw van een liedje\n14:30 Zelf schrijven in groepjes\n16:00 Presentaties',
 'Studio 2', 2, DATE_ADD(DATE(NOW()), INTERVAL 9 DAY) + INTERVAL 13 HOUR, 4,
 DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE(NOW()), INTERVAL 8 DAY), 'published'),

(4, 'Indie Night: The Harbour Lights',
 'De lokale indieband The Harbour Lights presenteert hun nieuwe album, met support van Velvet Dock.',
 '20:00 Deuren open\n20:30 Velvet Dock\n21:30 The Harbour Lights',
 'Grote zaal', 1, DATE_ADD(DATE(NOW()), INTERVAL 30 DAY) + INTERVAL 20 HOUR, 250,
 DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE(NOW()), INTERVAL 30 DAY) + INTERVAL 19 HOUR, 'published'),

(5, 'Theater: De Vuurtoren',
 'Een intieme voorstelling over een vuurtorenwachter en zijn laatste nacht op het eiland.',
 '19:30 Deuren open\n20:00 Voorstelling (75 minuten, geen pauze)',
 'Kleine zaal', 4, DATE_ADD(DATE(NOW()), INTERVAL 21 DAY) + INTERVAL 20 HOUR, 80,
 DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE(NOW()), INTERVAL 21 DAY) + INTERVAL 19 HOUR, 'published'),

(6, 'Open Mic Avond (concept)',
 'Iedereen mag drie nummers spelen. Inschrijven aan de bar.',
 NULL,
 'Café', 1, DATE_ADD(DATE(NOW()), INTERVAL 40 DAY) + INTERVAL 20 HOUR, 40,
 DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(DATE(NOW()), INTERVAL 40 DAY), 'draft');

-- Workshop Songwriting (capaciteit 4) is uitverkocht: 3 + 1 tickets.
INSERT INTO reservations (id, user_id, event_id, quantity, status, created_at, cancelled_at) VALUES
(1, 2, 1, 2, 'confirmed', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL),
(2, 2, 3, 3, 'confirmed', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(3, 2, 3, 1, 'confirmed', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(4, 2, 2, 1, 'cancelled', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

INSERT INTO tickets (reservation_id, code, status, used_at, used_by) VALUES
(1, 'SO-JAZZ-0001', 'valid', NULL, NULL),
(1, 'SO-JAZZ-0002', 'valid', NULL, NULL),
(2, 'SO-SONG-0001', 'valid', NULL, NULL),
(2, 'SO-SONG-0002', 'valid', NULL, NULL),
(2, 'SO-SONG-0003', 'used', NOW(), 1),
(3, 'SO-SONG-0004', 'valid', NULL, NULL),
(4, 'SO-COMD-0001', 'cancelled', NULL, NULL);
