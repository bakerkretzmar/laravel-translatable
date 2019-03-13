<?php

namespace Bakerkretzmar\Translatable\Tests;

use Bakerkretzmar\Translatable\Exceptions\AttributeNotTranslatable;

class TranslatableTest extends TestCase
{
    /** @var \Bakerkretzmar\Translatable\Tests\TestModel */
    protected $testModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = new TestModel();
    }

    /** @test */
    public function it_returns_a_fallback_locale_translation_when_getting_an_unknown_locale()
    {
        $this->app['config']->set('app.fallback_locale', 'en');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('EnglishTestValue', $this->testModel->getTranslation('name', 'fr'));
    }

    /** @test */
    public function it_can_disable_returning_a_fallback_locale_translation()
    {
        $this->app['config']->set('app.fallback_locale', 'en');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('', $this->testModel->getTranslation('name', 'fr', false));
    }

    /** @test */
    public function it_returns_a_fallback_locale_translation_when_getting_an_unknown_locale_and_fallback_option_is_true()
    {
        $this->app['config']->set('app.fallback_locale', 'en');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('EnglishTestValue', $this->testModel->getTranslationWithFallback('name', 'fr'));
    }

    /** @test */
    public function it_returns_an_empty_string_when_getting_an_unknown_locale_and_no_fallback_is_set()
    {
        // $this->app['config']->set('app.fallback_locale', '');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('', $this->testModel->getTranslationWithoutFallback('name', 'fr'));
    }

    /** @test */
    public function it_returns_an_empty_string_when_getting_an_unknown_locale_and_fallback_is_empty()
    {
        $this->app['config']->set('app.fallback_locale', '');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('', $this->testModel->getTranslation('name', 'fr'));
    }

    /** @test */
    public function it_can_get_a_translation_of_an_attribute_using_a_prefixed_property()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->save();

        $this->assertSame('EnglishTestValue', $this->testModel->trans_name);
    }

    /** @test */
    public function it_can_set_translations_when_creating_a_model()
    {
        $model = TestModel::create([
            'name' => ['en' => 'EnglishTestValue'],
        ]);

        $this->assertSame('EnglishTestValue', $model->trans_name);
    }

    /** @test */
    public function it_can_save_multiple_translations()
    {
        $this->app['config']->set('app.locale', 'en');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame('EnglishTestValue', $this->testModel->trans_name);
        $this->assertSame('FrenchTestValue', $this->testModel->getTranslation('name', 'fr'));
    }

    /** @test */
    public function it_returns_the_value_for_the_current_locale_when_using_a_prefixed_property()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        app()->setLocale('fr');

        $this->assertSame('FrenchTestValue', $this->testModel->trans_name);
    }

    /** @test */
    public function it_can_get_all_translations_for_an_attribute_at_once()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame([
            'en' => 'EnglishTestValue',
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_get_all_translations_for_all_translatable_attributes_at_once()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');

        $this->testModel->setTranslation('description', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('description', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame([
            'name' => [
                'en' => 'EnglishTestValue',
                'fr' => 'FrenchTestValue',
            ],
            'description' => [
                'en' => 'EnglishTestValue',
                'fr' => 'FrenchTestValue',
            ],
        ], $this->testModel->getTranslations());
    }

    /** @test */
    public function it_can_list_the_locales_with_translation_values_for_an_attribute()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame(['en', 'fr'], $this->testModel->getTranslatedLocales('name'));
    }

    /** @test */
    public function it_can_forget_a_translation()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame([
            'en' => 'EnglishTestValue',
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('name'));

        $this->testModel->forgetTranslation('name', 'en');

        $this->assertSame([
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_forget_all_translations_for_a_locale()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'fr', 'FrenchTestValue');

        $this->testModel->setTranslation('description', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('description', 'fr', 'FrenchTestValue');
        $this->testModel->save();

        $this->assertSame([
            'en' => 'EnglishTestValue',
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('name'));

        $this->assertSame([
            'en' => 'EnglishTestValue',
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('description'));

        $this->testModel->forgetAllTranslations('en');

        $this->assertSame([
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('name'));

        $this->assertSame([
            'fr' => 'FrenchTestValue',
        ], $this->testModel->getTranslations('description'));
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_translate_an_untranslatable_attribute()
    {
        $this->expectException(AttributeNotTranslatable::class);

        $this->testModel->setTranslation('untranslated', 'en', 'value');
    }

    /** @test */
    public function it_is_compatible_with_accessors_on_non_translatable_attributes()
    {
        $testModel = new class() extends TestModel {
            public function getDescriptionAttribute(): string
            {
                return 'accessorName';
            }
        };

        $this->assertEquals((new $testModel())->description, 'accessorName');
    }

    /** @test */
    public function it_can_use_accessors_on_translated_attributes()
    {
        $testModel = new class() extends TestModel {
            public function getNameAttribute($value): string
            {
                return "I just accessed {$value}";
            }
        };

        $testModel->setTranslation('name', 'en', 'EnglishTestValue');

        $this->assertEquals($testModel->trans_name, 'I just accessed EnglishTestValue');
    }

    /** @test */
    public function it_can_use_mutators_on_translated_attributes()
    {
        $testModel = new class() extends TestModel {
            public function setNameAttribute($value)
            {
                $this->attributes['name'] = "I just mutated {$value}";
            }
        };

        $testModel->setTranslation('name', 'en', 'EnglishTestValue');

        $this->assertEquals($testModel->trans_name, 'I just mutated EnglishTestValue');
    }

    /** @test */
    public function it_sets_translation_for_current_locale_using_prefix()
    {
        $model = TestModel::create([
            'name' => [
                'en' => 'EnglishTestValue',
                'fr' => 'FrenchTestValue',
            ],
        ]);

        app()->setLocale('en');

        $model->trans_name = 'updated_en';
        $this->assertEquals('updated_en', $model->trans_name);
        $this->assertEquals('FrenchTestValue', $model->getTranslation('name', 'fr'));

        app()->setLocale('fr');

        $model->trans_name = 'updated_fr';
        $this->assertEquals('updated_fr', $model->trans_name);
        $this->assertEquals('updated_en', $model->getTranslation('name', 'en'));
    }

    /** @test */
    public function it_can_set_multiple_translations_at_once()
    {
        $translations = ['nl' => 'hallo', 'en' => 'hello', 'kh' => 'សួរស្តី'];

        $this->testModel->setTranslations('name', $translations);
        $this->testModel->save();

        $this->assertEquals($translations, $this->testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_check_if_an_attribute_is_translatable()
    {
        $this->assertTrue($this->testModel->isTranslatable('name'));

        $this->assertFalse($this->testModel->isTranslatable('other'));
    }

    /** @test */
    public function it_can_check_if_an_attribute_has_a_translation_for_a_locale()
    {
        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'nl', null);
        $this->testModel->save();

        $this->assertTrue($this->testModel->hasTranslation('name', 'en'));

        $this->assertFalse($this->testModel->hasTranslation('name', 'pt'));
    }

    /** @test */
    public function it_can_set_a_translation_value_when_a_mutator_is_defined()
    {
        $testModel = (new class() extends TestModel {
            public function setNameAttribute($value)
            {
                $this->attributes['name'] = "I just mutated {$value}";
            }
        });

        $testModel->trans_name = 'hello';

        $expected = ['en' => 'I just mutated hello'];
        $this->assertEquals($expected, $testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_set_multiple_translation_values_when_a_mutator_is_defined()
    {
        $testModel = (new class() extends TestModel {
            public function setNameAttribute($value)
            {
                $this->attributes['name'] = "I just mutated {$value}";
            }
        });

        $translations = [
            'nl' => 'hallo',
            'en' => 'hello',
            'kh' => 'សួរស្តី',
        ];

        $testModel->setTranslations('name', $translations);

        $testModel->save();

        $expected = [
            'nl' => 'I just mutated hallo',
            'en' => 'I just mutated hello',
            'kh' => 'I just mutated សួរស្តី',
        ];

        $this->assertEquals($expected, $testModel->getTranslations('name'));
    }

    /** @test */
    public function it_can_translate_a_field_based_on_the_translations_of_another_one()
    {
        $testModel = (new class() extends TestModel {
            public function setDescriptionAttribute($value, $locale = 'en')
            {
                $this->attributes['description'] = $value.' '.$this->getTranslation('name', $locale);
            }
        });

        $testModel->setTranslations('name', [
            'nl' => 'wereld',
            'en' => 'world',
        ]);

        $testModel->setTranslations('description', [
            'nl' => 'hallo',
            'en' => 'hello',
        ]);

        $testModel->save();

        $expected = [
            'nl' => 'hallo wereld',
            'en' => 'hello world',
        ];

        $this->assertEquals($expected, $testModel->getTranslations('description'));
    }

    /** @test */
    public function it_can_handle_null_values()
    {
        $testModel = (new class() extends TestModel {
            public function setAttributesExternally(array $attributes)
            {
                $this->attributes = $attributes;
            }
        });

        $testModel->setAttributesExternally(['name' => json_encode(null), 'description' => null]);

        $this->assertEquals('', $testModel->trans_name);
        $this->assertEquals('', $testModel->trans_description);
    }

    /** @test */
    public function it_can_get_all_translations_using_accessor()
    {
        $translations = ['nl' => 'hallo', 'en' => 'hello'];

        $this->testModel->setTranslations('name', $translations);
        $this->testModel->save();

        $this->assertEquals([
           'name' => [
                'nl' => 'hallo',
                'en' => 'hello'
            ],
           'description' => [],
        ], $this->testModel->translations);
    }

    /** @test */
    public function it_returns_the_fallback_locale_translation_when_getting_an_empty_translation_from_a_locale()
    {
        $this->app['config']->set('app.fallback_locale', 'en');

        $this->testModel->setTranslation('name', 'en', 'EnglishTestValue');
        $this->testModel->setTranslation('name', 'nl', null);
        $this->testModel->save();

        $this->assertSame('EnglishTestValue', $this->testModel->getTranslation('name', 'nl'));
    }

    /** @test */
    public function it_returns_an_empty_string_if_translation_value_is_set_to_zero()
    {
        $this->testModel->setTranslation('name', 'nl', '0');
        $this->testModel->save();

        $this->assertSame('', $this->testModel->getTranslation('name', 'nl'));
    }

    /** @test */
    public function it_returns_a_fallback_value_if_translation_value_is_set_to_zero()
    {
        $this->app['config']->set('app.fallback_locale', 'en');

        $this->testModel->setTranslation('name', 'en', '1');
        $this->testModel->setTranslation('name', 'nl', '0');
        $this->testModel->save();

        $this->assertSame('1', $this->testModel->getTranslation('name', 'nl'));
    }

    /** @test */
    function it_can_use_a_prefix_from_the_config()
    {
        $this->app['config']->set('app.translatable_prefix', 'localized_');

        $testModel = new TestModel();

        $testModel->setTranslation('name', 'en', 'EnglishTestValue');

        $this->assertSame('EnglishTestValue', $testModel->localized_name);
    }
}
