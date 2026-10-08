<?php

// Controller: evenementen beheren (alleen medewerkers, zie index.php)

// Datum zoals in <input type="datetime-local">, bijv. 2026-10-18T20:00
const FORM_DATE_FORMAT = 'Y-m-d\TH:i';

function showStaffEvents()
{
    view('staff/events/index', [
        'title'  => 'Evenementbeheer',
        'events' => getAllEvents(),
    ]);
}

function showNewEventForm()
{
    // Nieuw evenement: concept met 100 plaatsen
    showEventForm(null, ['status' => 'draft', 'capacity' => '100'], []);
}

function handleCreateEvent()
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

function showEditEventForm()
{
    $event = findEventOr404();

    // Datums omzetten naar het formaat van het formulier
    $values = $event;
    $values['starts_at'] = date(FORM_DATE_FORMAT, strtotime($event['starts_at']));
    $values['sale_starts_at'] = date(FORM_DATE_FORMAT, strtotime($event['sale_starts_at']));
    $values['sale_ends_at'] = date(FORM_DATE_FORMAT, strtotime($event['sale_ends_at']));

    showEventForm($event, $values, []);
}

function handleUpdateEvent()
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

function handleDeleteEvent()
{
    $event = findEventOr404();

    // Met reserveringen niet verwijderen, anders zijn de tickets van mensen weg
    if (eventHasReservations($event['id'])) {
        setFlash('error', 'Dit evenement heeft al reserveringen en kan niet worden verwijderd. Zet de status op "Geannuleerd".');
    } else {
        deleteEvent($event['id']);
        setFlash('success', 'Evenement "' . $event['title'] . '" is verwijderd.');
    }

    redirect('staff/events');
}

// Evenement uit de URL ophalen, anders 404.
function findEventOr404()
{
    $event = findEvent(inputInt('id'));

    if ($event === null) {
        showError(404, 'Dit evenement bestaat niet.');
    }
    return $event;
}

// Alle velden checken. Geeft ['veld' => 'fout'] terug, leeg = alles goed.
// $existingEvent is null bij een nieuw evenement.
function checkEventForm($existingEvent)
{
    $errors = [];

    // 1. Verplichte velden
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

    // 2. Niet te lang
    if (strlen(inputText('title')) > 150) {
        $errors['title'] = 'De naam mag maximaal 150 tekens lang zijn.';
    }
    if (strlen(inputText('location')) > 150) {
        $errors['location'] = 'De locatie mag maximaal 150 tekens lang zijn.';
    }

    // 3. Bestaat de categorie en is de status goed?
    if (!isset($errors['category_id']) && !categoryExists(inputInt('category_id'))) {
        $errors['category_id'] = 'Kies een bestaande categorie.';
    }
    if (!isset(EVENT_STATUS_LABELS[inputText('status')])) {
        $errors['status'] = 'Kies een geldige status.';
    }

    // 4. Capaciteit: niet minder dan het aantal verkochte tickets
    $capacity = inputInt('capacity');
    if (!isset($errors['capacity'])) {
        if ($capacity === null || $capacity > 100000) {
            $errors['capacity'] = 'De capaciteit moet een getal zijn tussen 1 en 100000.';
        } elseif ($existingEvent !== null) {
            $sold = soldTickets($existingEvent['id']);
            if ($capacity < $sold) {
                $errors['capacity'] = 'Er zijn al ' . $sold . ' tickets verkocht. De capaciteit kan niet lager zijn dan ' . $sold . '.';
            }
        }
    }

    // 5. Echte datums?
    foreach (['starts_at', 'sale_starts_at', 'sale_ends_at'] as $field) {
        if (!isset($errors[$field]) && !isValidDate(inputText($field), FORM_DATE_FORMAT)) {
            $errors[$field] = 'Dit is geen geldige datum.';
        }
    }

    // 6. Kloppen de datums met elkaar?
    if (!isset($errors['starts_at']) && !isset($errors['sale_starts_at']) && !isset($errors['sale_ends_at'])) {
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

// Ingevulde velden klaarzetten voor de database (zelfde volgorde als in de SQL).
function eventFormData()
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

// Formulier tonen, voor nieuw én bewerken.
function showEventForm($event, $values, $errors, $statusCode = 200)
{
    if ($event === null) {
        $title = 'Nieuw evenement';
    } else {
        $title = 'Evenement bewerken';
    }

    view('staff/events/form', [
        'title'      => $title,
        'event'      => $event,
        'values'     => $values,
        'errors'     => $errors,
        'categories' => getCategories(),
    ], $statusCode);
}
