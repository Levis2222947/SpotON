<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Een bedrijfsregel is overtreden (bijv. "niet genoeg plaatsen").
 * De melding is bedoeld voor de gebruiker en mag dus getoond worden.
 */
final class BusinessRuleException extends RuntimeException
{
}
