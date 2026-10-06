<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;

/**
 * Controleert formulierinvoer. Per veld wordt alleen de eerste fout bewaard,
 * zodat de gebruiker één duidelijke melding per veld ziet.
 *
 * Voorbeeld:
 *   $v = new Validator($_POST);
 *   $v->required('email', 'E-mailadres')->email('email', 'E-mailadres');
 *   if ($v->fails()) { ... $v->errors() ... }
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function value(string $field): string
    {
        $value = $this->data[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->addError($field, "{$label} is verplicht.");
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        if ($this->value($field) !== '' && filter_var($this->value($field), FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, "{$label} is geen geldig e-mailadres.");
        }
        return $this;
    }

    public function minLength(string $field, string $label, int $min): self
    {
        if ($this->value($field) !== '' && mb_strlen($this->value($field)) < $min) {
            $this->addError($field, "{$label} moet minimaal {$min} tekens lang zijn.");
        }
        return $this;
    }

    public function maxLength(string $field, string $label, int $max): self
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->addError($field, "{$label} mag maximaal {$max} tekens lang zijn.");
        }
        return $this;
    }

    public function integer(string $field, string $label, int $min, int $max): self
    {
        $value = $this->value($field);
        if ($value === '') {
            return $this;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "{$label} moet een heel getal zijn.");
        } elseif ((int) $value < $min || (int) $value > $max) {
            $this->addError($field, "{$label} moet tussen {$min} en {$max} liggen.");
        }
        return $this;
    }

    /** Controleert een datum/tijd in het opgegeven formaat, bijv. 'Y-m-d' of 'Y-m-d\TH:i'. */
    public function dateFormat(string $field, string $label, string $format): self
    {
        $value = $this->value($field);
        if ($value !== '' && self::parseDate($value, $format) === null) {
            $this->addError($field, "{$label} is geen geldige datum.");
        }
        return $this;
    }

    public function in(string $field, string $label, array $allowed): self
    {
        if ($this->value($field) !== '' && !in_array($this->value($field), $allowed, true)) {
            $this->addError($field, "Kies een geldige waarde voor {$label}.");
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $message): self
    {
        if ($this->value($field) !== $this->value($otherField)) {
            $this->addError($field, $message);
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] ??= $message;
        return $this;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function parseDate(string $value, string $format): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
        return $date !== false && $date->format($format) === $value ? $date : null;
    }
}
