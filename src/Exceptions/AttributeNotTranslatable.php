<?php

namespace Bakerkretzmar\Translatable\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AttributeNotTranslatable extends Exception
{
    public static function make(string $key, Model $model)
    {
        $translations = implode(', ', $model->translatable);

        return new static("Cannot translate attribute `{$key}` as it’s not one of the translatable attributes: {$translations}");
    }
}
