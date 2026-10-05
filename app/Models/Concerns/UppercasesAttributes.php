<?php

namespace App\Models\Concerns;

/**
 * Stores the listed text attributes in capitals so set-up records are
 * uniform no matter how they were typed. Models list their own fields in
 * `protected array $uppercase`; the default is name and code.
 */
trait UppercasesAttributes
{
    public static function bootUppercasesAttributes(): void
    {
        static::saving(function ($model) {
            foreach ($model->uppercase ?? ['name', 'code'] as $attribute) {
                $value = $model->getAttribute($attribute);

                if (is_string($value)) {
                    $model->setAttribute($attribute, mb_strtoupper(trim($value)));
                }
            }
        });
    }
}
