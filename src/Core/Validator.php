<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rule-string validator. Rules are pipe-delimited, e.g.
 *   'email' => 'required|email|max:150'
 *   'units' => 'required|integer|min:1|max:20'
 *
 * Supported rules: required, nullable, string, email, integer, numeric,
 * boolean, date, in:a,b,c, min:n, max:n (length for strings, value for
 * numbers), same:field, different:field, confirmed, regex:/.../, digits:n,
 * lat, lng.
 *
 * Kept deliberately small and dependency-free; covered by unit tests.
 */
final class Validator
{
    /** @var array<string, mixed> */
    private array $data;
    /** @var array<string, string> */
    private array $rules;
    /** @var array<string, string> */
    private array $messages;
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, mixed> */
    private array $validated = [];

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages custom "field.rule" => message
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function passes(): bool
    {
        $this->errors = [];
        $this->validated = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = array_filter(array_map('trim', explode('|', $ruleString)));
            $value = $this->data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }

            $isNullable = in_array('nullable', $rules, true);
            $isRequired = in_array('required', $rules, true);
            $isEmpty = $value === null || $value === '' || $value === [];

            if ($isEmpty) {
                if ($isRequired) {
                    $this->addError($field, 'required', 'The :field field is required.');
                } elseif ($isNullable || !$isRequired) {
                    $this->validated[$field] = $isNullable ? null : $value;
                }
                continue;
            }

            $failed = false;
            foreach ($rules as $rule) {
                if (in_array($rule, ['required', 'nullable'], true)) {
                    continue;
                }
                if (!$this->applyRule($field, $rule, $value)) {
                    $failed = true;
                    break;
                }
            }

            if (!$failed) {
                $this->validated[$field] = $value;
            }
        }

        return $this->errors === [];
    }

    private function applyRule(string $field, string $rule, mixed $value): bool
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($name) {
            case 'string':
                return $this->check($field, $name, is_string($value), 'The :field must be text.');

            case 'email':
                return $this->check($field, $name, filter_var($value, FILTER_VALIDATE_EMAIL) !== false, 'Enter a valid email address.');

            case 'integer':
                return $this->check($field, $name, preg_match('/^-?\d+$/', (string) $value) === 1, 'The :field must be a whole number.');

            case 'numeric':
                return $this->check($field, $name, is_numeric($value), 'The :field must be a number.');

            case 'boolean':
                return $this->check($field, $name, in_array((string) $value, ['0', '1', 'true', 'false', 'on', 'off'], true), 'The :field must be true or false.');

            case 'date':
                return $this->check($field, $name, strtotime((string) $value) !== false, 'The :field must be a valid date.');

            case 'digits':
                return $this->check($field, $name, preg_match('/^\d{' . (int) $param . '}$/', (string) $value) === 1, "The :field must be {$param} digits.");

            case 'in':
                $allowed = explode(',', (string) $param);
                return $this->check($field, $name, in_array((string) $value, $allowed, true), 'The selected :field is invalid.');

            case 'min':
                $ok = is_numeric($value) ? ($value + 0) >= (float) $param : mb_strlen((string) $value) >= (int) $param;
                return $this->check($field, $name, $ok, is_numeric($value) ? "The :field must be at least {$param}." : "The :field must be at least {$param} characters.");

            case 'max':
                $ok = is_numeric($value) ? ($value + 0) <= (float) $param : mb_strlen((string) $value) <= (int) $param;
                return $this->check($field, $name, $ok, is_numeric($value) ? "The :field must not exceed {$param}." : "The :field must not exceed {$param} characters.");

            case 'same':
                return $this->check($field, $name, ($this->data[$param] ?? null) === $value, "The :field must match {$param}.");

            case 'different':
                return $this->check($field, $name, ($this->data[$param] ?? null) !== $value, "The :field must be different from {$param}.");

            case 'confirmed':
                return $this->check($field, $name, ($this->data[$field . '_confirmation'] ?? null) === $value, 'The :field confirmation does not match.');

            case 'regex':
                return $this->check($field, $name, @preg_match((string) $param, (string) $value) === 1, 'The :field format is invalid.');

            case 'lat':
                return $this->check($field, $name, is_numeric($value) && $value >= -90 && $value <= 90, 'Invalid latitude.');

            case 'lng':
                return $this->check($field, $name, is_numeric($value) && $value >= -180 && $value <= 180, 'Invalid longitude.');

            default:
                return true; // unknown rule: ignore rather than hard-fail
        }
    }

    private function check(string $field, string $rule, bool $condition, string $default): bool
    {
        if ($condition) {
            return true;
        }
        $this->addError($field, $rule, $default);

        return false;
    }

    private function addError(string $field, string $rule, string $default): void
    {
        if (isset($this->errors[$field])) {
            return; // first error per field only
        }
        $message = $this->messages["{$field}.{$rule}"] ?? $this->messages[$field] ?? $default;
        $this->errors[$field] = str_replace(':field', $this->label($field), $message);
    }

    private function label(string $field): string
    {
        return str_replace('_', ' ', $field);
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        return $this->validated;
    }
}
