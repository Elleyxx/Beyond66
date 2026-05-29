<?php

class Validator
{
    public static function required(array $payload, array $fields): array
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($payload[$field]) || trim((string)$payload[$field]) === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
