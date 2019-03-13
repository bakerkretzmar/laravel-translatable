<?php

namespace Bakerkretzmar\Translatable\Events;

use Illuminate\Database\Eloquent\Model;

class TranslationUpdated
{
    /** @var \Bakerkretzmar\Translatable\Translatable */
    public $model;

    /** @var string */
    public $key;

    /** @var string */
    public $locale;

    public $oldValue;
    public $newValue;

    public function __construct(Model $model, string $key, string $locale, $oldValue, $newValue)
    {
        $this->model = $model;

        $this->key = $key;

        $this->locale = $locale;

        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
}
