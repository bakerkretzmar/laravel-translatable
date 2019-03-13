<?php

namespace Bakerkretzmar\Translatable;

use Illuminate\Support\Str;

use Bakerkretzmar\Translatable\Events\TranslationUpdated;
use Bakerkretzmar\Translatable\Exceptions\AttributeNotTranslatable;

trait HasTranslations
{
    /**
     * The string to prefix translatable attribute shortcuts with.
     *
     * @var string
     */
    protected $prefix = 'trans_';

    /**
     * Initialize the trait.
     *
     * @return void
     */
    public function initializeHasTranslations()
    {
        if (config('app.translatable_prefix')) {
            $this->prefix = config('app.translatable_prefix');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Overrides
    |--------------------------------------------------------------------------
    */

    /**
     * Provide shortcuts to access translatable attributes directly using
     * a prefix. If the prefixed attribute is translatable, return the
     * correct translation.
     *
     * Overrides built-in Eloquent model method.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! Str::startsWith($key, $this->prefix) ||
            ! $this->isTranslatable(Str::after($key, $this->prefix))) {
            return parent::getAttribute($key);
        }

        return $this->getTranslation(Str::after($key, $this->prefix), $this->getLocale());
    }

    /**
     * Provide shortcuts to set translatable attributes directly using
     * a prefix.
     *
     * Overrides built-in Eloquent model method.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // Pass arrays and untranslatable attributes to the parent method.
        if (is_array($value) ||
            ! Str::startsWith($key, $this->prefix) ||
            ! $this->isTranslatable(Str::after($key, $this->prefix))) {
            return parent::setAttribute($key, $value);
        }

        // If the attribute is translatable, set a translation for the current locale
        return $this->setTranslation(Str::after($key, $this->prefix), $this->getLocale(), $value);
    }

    /**
     * Get the casts array.
     *
     * Overrides built-in Eloquent model method.
     *
     * @return array
     */
    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            array_fill_keys($this->translatable, 'array')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the translated value of the given attribute for the given locale.
     *
     * @param  string  $key
     * @param  string  $locale
     * @param  bool    $useFallbackLocale  (optional)
     * @return string
     */
    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): string
    {
        $locale = $this->normalizeLocale($key, $locale, $useFallbackLocale);

        $translation = $this->getTranslations($key)[$locale] ?? '';

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        }

        return $translation;
    }

    /**
     * Alias of getTranslation().
     *
     * @param  string  $key
     * @param  string  $locale  (optional)
     * @return string
     */
    public function translate(string $key, string $locale = ''): string
    {
        return $this->getTranslation($key, $locale);
    }

    /**
     * Get the translated value of the given attribute, and use the fallback
     * locale if no translation is set.
     *
     * @param  string  $key
     * @param  string  $locale
     * @return string
     */
    public function getTranslationWithFallback(string $key, string $locale): string
    {
        return $this->getTranslation($key, $locale, true);
    }

    /**
     * Get the translated value for the given attribute, and do not use the
     * fallback locale if no translation is set.
     *
     * @param  string  $key
     * @param  string  $locale
     * @return string
     */
    public function getTranslationWithoutFallback(string $key, string $locale): string
    {
        return $this->getTranslation($key, $locale, false);
    }

    /**
     * Get all the translations for the attribute with the given key, or if no key
     * is passed, all the translations for all translatable attributes.
     *
     * @param  string  $key  (optional)
     * @return array
     */
    public function getTranslations(string $key = null): array
    {
        if (isset($key)) {
            $this->ensureTranslatable($key);

            return array_filter(json_decode($this->getAttributes()[$key] ?? '' ?: '{}', true) ?: [], function ($value) {
                return ! empty($value);
            });
        }

        return array_reduce($this->translatable, function ($result, $item) {
            $result[$item] = $this->getTranslations($item);

            return $result;
        });
    }

    /**
     * Set the translation value for the given key in the given locale.
     *
     * @param  string $key
     * @param  string $locale
     * @param  mixed  $value
     * @return self
     */
    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->ensureTranslatable($key);

        $translations = $this->getTranslations($key);

        $oldValue = $translations[$locale] ?? '';

        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$key];
        }

        $translations[$locale] = $value;

        $this->attributes[$key] = $this->asJson($translations);

        event(new TranslationUpdated($this, $key, $locale, $oldValue, $value));

        return $this;
    }

    /**
     * Set multiple translations for the attribute with the given key.
     *
     * @param  string  $key
     * @param  array   $translations
     * @return self
     */
    public function setTranslations(string $key, array $translations): self
    {
        $this->ensureTranslatable($key);

        foreach ($translations as $locale => $translation) {
            $this->setTranslation($key, $locale, $translation);
        }

        return $this;
    }

    /**
     * Remove the translation value of the given attribute in the given locale.
     *
     * @param  string  $key
     * @param  string  $locale
     * @return self
     */
    public function forgetTranslation(string $key, string $locale): self
    {
        $translations = $this->getTranslations($key);

        unset($translations[$locale]);

        $this->setAttribute($key, $translations);

        return $this;
    }

    /**
     * Remove all translation values from all attributes for the given locale.
     *
     * @param  string  $locale
     * @return self
     */
    public function forgetAllTranslations(string $locale): self
    {
        collect($this->translatable)->each(function (string $attribute) use ($locale) {
            $this->forgetTranslation($attribute, $locale);
        });

        return $this;
    }

    /**
     * Eloquent accessor to get all translations of all translatable attributes.
     *
     * @return array
     */
    public function getTranslationsAttribute(): array
    {
        return collect($this->translatable)
            ->mapWithKeys(function (string $key) {
                return [$key => $this->getTranslations($key)];
            })
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the app’s current locale.
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return config('app.locale');
    }

    /**
     * Get the locale key to use for translation.
     *
     * @param  string  $key
     * @param  string  $locale
     * @param  bool    $useFallbackLocale
     * @return string
     */
    protected function normalizeLocale(string $key, string $locale, bool $useFallbackLocale): string
    {
        if (in_array($locale, $this->getTranslatedLocales($key))) {
            return $locale;
        }

        if ($useFallbackLocale && ! is_null($fallbackLocale = config('app.fallback_locale'))) {
            return $fallbackLocale;
        }

        return $locale;
    }

    /**
     * Determine if the given key is one of the model's translatable attributes.
     *
     * @param  string  $key
     * @return bool
     */
    public function isTranslatable(string $key): bool
    {
        return in_array($key, $this->translatable);
    }

    /**
     * Ensure that the given key is one of the model's translatable attributes.
     *
     * @param  string $key
     * @return void
     *
     * @throws \Bakerkretzmar\Translatable\Exceptions\AttributeNotTranslatable
     */
    protected function ensureTranslatable(string $key): void
    {
        if (! $this->isTranslatable($key)) {
            throw AttributeNotTranslatable::make($key, $this);
        }
    }

    /**
     * Determine whether the given attribute is translated in the given locale,
     * or if no locale is passed, in the current locale.
     *
     * @param  string  $key
     * @param  string  $locale  (optional)
     * @return bool
     */
    public function hasTranslation(string $key, string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return isset($this->getTranslations($key)[$locale]);
    }

    /**
     * Get the locales of the translated values of the attribute with the given key.
     *
     * @param  string  $key
     * @return array
     */
    public function getTranslatedLocales(string $key): array
    {
        return array_keys($this->getTranslations($key));
    }
}
