<?php

/*
 * Controller: evenementen bekijken. Deze pagina's zijn voor iedereen.
 */

/** Home: lijst met evenementen, met zoeken op datum en categorie. */
function showEventList(): void
{
    $date = inputText('date');
    $categoryId = inputInt('category');
    $filterError = '';

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

/** Detailpagina van één evenement. */
function showEventDetail(): void
{
    $event = findVisibleEvent(inputInt('id') ?? 0);

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
