<?php

namespace Bakerkretzmar\Translatable\Tests;

use Bakerkretzmar\Translatable\HasTranslations;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasTranslations;

    protected $table = 'test_models';

    protected $guarded = [];

    public $timestamps = false;

    public $translatable = [
        'name',
        'description'
    ];
}
