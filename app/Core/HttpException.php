<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Fout die als nette foutpagina (403, 404, 405, 419) wordt getoond. */
final class HttpException extends RuntimeException
{
    public function __construct(public readonly int $status, string $message)
    {
        parent::__construct($message, $status);
    }

    public static function forbidden(string $message = 'Je hebt geen toegang tot deze pagina.'): self
    {
        return new self(403, $message);
    }

    public static function notFound(string $message = 'Deze pagina bestaat niet (meer).'): self
    {
        return new self(404, $message);
    }
}
