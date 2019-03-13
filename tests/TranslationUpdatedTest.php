<?php

namespace Bakerkretzmar\Translatable\Tests;

use Bakerkretzmar\Translatable\Events\TranslationUpdated;

class TranslationUpdatedTest extends TestCase
{
    /** @var \Bakerkretzmar\Translatable\Tests\TestModel */
    protected $testModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = new TestModel();
    }

    /** @test */
    public function it_fires_an_event_when_a_translation_is_updated()
    {
        $this->expectsEvents(TranslationUpdated::class);

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
    }
}
