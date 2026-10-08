<?php

/*
 * Controller voor medewerkers: evenementen aanmaken, wijzigen en verwijderen.
 * Alleen voor medewerkers (dat wordt gecontroleerd in public/index.php).
 */

// Zo ziet de datum eruit in een <input type="datetime-local">, bijv. 2026-10-18T20:00
const FORM_DATE_FORMAT = 'Y-m-d\TH:i';

function showStaffEvents(): void
{
    view('staff/events/index', [
        'title'  => 'Evenementbeheer',
        'events' => getAllEvents(),
    ]);
}

function showNewEventForm(): void
{
    showEventForm(null, ['status' => 'draft', 'capacity' => '100'], []);
}

function handleCreateEvent(): void
{
    $errors = checkEventForm(null);

    if ($errors !== []) {
        showEventForm(null, $_POST, $errors, 422);
        return;
    }

    $data = eventFormData();
    $eventId = createEvent($data);
    setFlash('success', 'Evenement "' . $data['title'] . '" is aangemaakt.');
    redirect('staff/events/edit', ['id' => $eventId]);
}

function showEditEventForm(): void
{
    $event = findEventOr404();

    // Datums uit de database omzetten naar het formaat van het formulier
    $values = $event;
    foreach (['starts_at', 'sale_starts_at', 'sale_ends_at'] as $field) {
        $values[$field] = date(FORM_DATE_FORMAT, strtotime($event[$field]));
    }

    showEventForm($event, $values, []);
}

function handleUpdateEvent(): void
{
    $event = findEventOr404();
    $errors = checkEventForm($event);

    if ($errors !== []) {
        showEventForm($event, $_POST, $errors, 422);
        return;
    }

    $data = eventFormData();
    updateEvent($event['id'], $data);
    setFlash('success', 'Evenement "' . $data['title'] . '" is opgeslagen.');
    redirect('staff/events');
}

function handleDeleteEvent(): void
{
    $event = findEventOr404();

    // Een evenement met reserveringen verwijderen we niet, anders verdwijnen de tickets.
    if (eventHasReservations($event['id'])) {
        setFlash('error', 'Dit evenement heeft al reserveringen en kan niet worden verwijderd. Zet de status op "Geannuleerd".');
    } else {
        deleteEvent($event['id']);
        setFlash('success', 'Evenement "' . $event['title'] . '" is verwijderd.');
    }
    redirect('staff/events');
}

function findEventOr404(): array
{
    $event = findEvent(inputInt('id') ?? 0);
    if ($event === null) {
        showError(404, 'Dit evenement bestaat niet.');
    }
    return $event;
}

/**
 * Controleert alle velden van het formulier.
 * Geeft een lijst met fouten terug: ['veldnaam' => 'foutmelding']. Leeg = alles goed.
 * $existingEvent is null bij een nieuw evenement.
 */
function checkEventForm(?array $existingEvent): array
{
    $errors = [];

    $requiredFields = [
        'title'          => 'Vul de naam van het evenement in.',
        'description'    => 'Vul een beschrijving in.',
        'location'       => 'Vul de locatie in.',
        'category_id'    => 'Kies een categorie.',
        'starts_at'      => 'Vul de datum en tijd in.',
        'capacity'       => 'Vul de capaciteit in.',
        'sale_starts_at' => 'Vul de start van de verkoop in.',
        'sale_ends_at'   => 'Vul het einde van de verkoop in.',
    ];
    foreach ($requiredFields as $field => $message) {
        if (inputText($field) === '') {
            $errors[$field] = $message;
        }
    }

    if (strlen(inputText('title')) > 150) {
        $errors['title'] = 'De naam mag maximaal 150 tekens lang zijn.';
    }
    if (strlen(inputText('location')) > 150) {
        $errors['location'] = 'De locatie mag maximaal 150 tekens lang zijn.';
    }

    if (!isset($errors['category_id']) && !categoryExists(inputInt('category_id') ?? 0)) {
        $errors['category_id'] = 'Kies een bestaande categorie.';
    }

    if (!array_key_exists(inputText('status'), EVENT_STATUS_LABELS)) {
        $errors['status'] = 'Kies een geldige status.';
    }

    // Capaciteit: een heel getal, en nooit minder dan het aantal tickets dat al verkocht is
    $capacity = inputInt('capacity');
    if (!isset($errors['capacity']) && ($capacity === null || $capacity > 100000)) {
        $errors['capacity'] = 'De capaciteit moet een getal zijn tussen 1 en 100000.';
    } elseif ($existingEvent !== null && $capacity !== null) {
        $sold = soldTickets($existingEvent['id']);
        if ($capacity < $sold) {
            $errors['capacity'] = "Er zijn al {$sold} tickets verkocht. De capaciteit kan niet lager zijn dan {$sold}.";
        }
    }

    // Datums controleren
    foreach (['starts_at', 'sale_starts_at', 'sale_ends_at'] as $field) {
        if (!isset($errors[$field]) && !isValidDate(inputText($field), FORM_DATE_FORMAT)) {
            $errors[$field] = 'Dit is geen geldige datum.';
        }
    }

    if (!isset($errors['starts_at'], $errors['sale_starts_at'], $errors['sale_ends_at'])) {
        $startsAt = strtotime(inputText('starts_at'));
        $saleStart = strtotime(inputText('sale_starts_at'));
        $saleEnd = strtotime(inputText('sale_ends_at'));

        if ($existingEvent === null && $startsAt <= time()) {
            $errors['starts_at'] = 'Een nieuw evenement moet in de toekomst zijn.';
        }
        if ($saleStart >= $saleEnd) {
            $errors['sale_ends_at'] = 'Het einde van de verkoop moet na de start van de verkoop zijn.';
        } elseif ($saleEnd > $startsAt) {
            $errors['sale_ends_at'] = 'De verkoop moet stoppen voordat het evenement begint.';
        }
    }

    return $errors;
}

/** Zet de ingevulde velden klaar om op te slaan in de database. */
function eventFormData(): array
{
    return [
        'title'          => inputText('title'),
        'description'    => inputText('description'),
        'program'        => inputText('program'),
        'location'       => inputText('location'),
        'category_id'    => inputInt('category_id'),
        'starts_at'      => date('Y-m-d H:i:s', strtotime(inputText('starts_at'))),
        'capacity'       => inputInt('capacity'),
        'sale_starts_at' => date('Y-m-d H:i:s', strtotime(inputText('sale_starts_at'))),
        'sale_ends_at'   => date('Y-m-d H:i:s', strtotime(inputText('sale_ends_at'))),
        'status'         => inputText('status'),
    ];
}

function showEventForm(?array $event, array $values, array $errors, int $statusCode = 200): void
{
    view('staff/events/form', [
        'title'      => $event === null ? 'Nieuw evenement' : 'Evenement bewerken',
        'event'      => $event,
        'values'     => $values,
        'errors'     => $errors,
        'categories' => getCategories(),
    ], $statusCode);
}
