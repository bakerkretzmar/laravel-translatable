<?php

namespace Bakerkretzmar\Translatable\Exceptions;

use Exception;

class ShortcutNotPrefixed extends Exception
{
    public function __construct(string $key)
    {
        return new static("Cannot access translated `$key` attribute directly. To access translation directly as a property, prefix the attribute name with 'trans_': e.g. `trans_$key`");
    }

    // public static function make(string $key)
    // {
    //     return new static("Cannot access translated `$key` attribute directly. To access translation directly as a property, prefix the attribute name with 'trans_': e.g. `trans_$key`");
    // }
}
