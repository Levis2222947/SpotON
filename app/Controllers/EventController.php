<?php

// Controller: evenementen bekijken (voor iedereen)

// Homepagina met zoeken op datum en categorie.
function showEventList()
{
    $date = inputText('date');
    $categoryId = inputInt('category');
    $filterError = '';

    // Ongeldige datum? Dan niet op datum zoeken.
    if ($date !== '' && !isValidDate($date, 'Y-m-d')) {
        $filterError = 'De gekozen datum is ongeldig. Er wordt niet op datum gezocht.';
        $date = '';
    }

    view('events/index', [
        'title'       => 'Evenementen',
        'events'      => searchEvents($date, $categoryId),
        'categories'  => getCategories(),
        'filters'     => ['date' => $date, 'category' => $categoryId],
        'isFiltered'  => $date !== '' || $categoryId !== null,
        'filterError' => $filterError,
    ]);
}

// Detailpagina van één evenement.
function showEventDetail()
{
    $event = findVisibleEvent(inputInt('id'));

    if ($event === null) {
        showError(404, 'Dit evenement bestaat niet of is niet meer zichtbaar.');
    }

    view('events/show', [
        'title'      => $event['title'],
        'event'      => $event,
        'saleStatus' => saleStatus($event),
        'program'    => programLines($event),
    ]);
}
