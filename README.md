Another trait to make Eloquent models translatable
==================================================

[![MIT License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis CI build status](https://img.shields.io/travis/bakerkretzmar/laravel-translatable/master.svg?style=flat-square)](https://travis-ci.org/bakerkretzmar/laravel-translatable)
<!-- [![StyleCI](https://styleci.io/repos/55690447/shield?branch=master)](https://styleci.io/repos/55690447) -->

This package is a fork of [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable), which provides a trait to make Eloquent models translatable. That package allows a translated attribute, like `name`, to store many different translation values in a JSON column and conveniently always return the correct one for the current app locale. Almost too conveniently...

Using Spatie's package the translated property always returns a string, so there's no way to retrieve the whole array out of the database using a _property_ on the model. Likewise, there's no way to access the property itself _as_ an array, so fun little tricks like Laravel's [`data_get()`](https://github.com/laravel/framework/blob/5.8/src/Illuminate/Support/helpers.php#L489) helper don't work anymore.

This package prefixes the translated properties, so they look something like `$model->trans_name` instead of `$model->name`. This is slightly less convenient, but allows you to easily continue working with the underlying array of translation values—as an array.

One example of where this might be useful (read: my obscure edge case) is if you're using a single translation value as one of your index columns on a [Laravel Nova](https://nova.laravel.com) resource. If you've got something like a `name_en` accessor set up to get a specific translation value directly, you can't sort by that property in Nova because it doesn't actually exist as a column in the database. Nova uses `data_get()` behind the scenes so that you're able to access JSON properties with dot or arrow notation, like `name.en`, so you could have an 'English name' and 'French name' column in Nova, and both of them could be sortable, but they're actually just one `name` JSON column in the database.

**TL;DR**: this package leaves the translatable properties as arrays and adds a prefixed shortcut property to automatically get the current translation instead.

Task | `spatie/laravel-translatable` | `bakerkretzmar/laravel-translatable`
--- | --- | ---
Get the `name` attribute's translation for the current locale | `$model->name` | `$model->trans_name`
Get an array of all the translations for the `name` attribute | `$model->getTranslations('name')` | `$model->name`
Get a specific translation of the `name` attribute | `$model->translate('name', 'fr')` | `$model->translate('name', 'fr')`
Get a specific translation, treating the `name` property as an array | :cry: | `data_get($model, 'name.en')`

Installation
------------

``` bash
composer require bakerkretzmar/laravel-translatable
```

Setup
-----

To make a model translatable:

- Use the `Bakerkretzmar\Translatable\HasTranslations` trait.
- Add a public property `$translatable` with an array of names of the attributes you want to translate.
- Make sure all the model's translatable attributes are `json` columns in your databse. If your database doesn't support `json` columnes, use `text`.

If you want to use a translatable property prefix other than `trans_`, just specify it in a `translatable_prefix` key in your `config/app.php`.

Usage
-----

#### Retrieving translations

Just access the prefixed property for the translated attribute. For example, to get the `name` property in the current language:

```php
$model->trans_name;
```

Or, use a method:

```php
$model->translate('name');

$model->getTranslation('name', 'fr');
```

#### Validation

**To be confirmed, haven't tested this yet**. If you need to validate an attribute for uniqueness before saving or updating the DB, check out [laravel-unique-translation](https://github.com/codezero-be/laravel-unique-translation), which is made specifically for `laravel-translatable`.

#### Everything else

The rest is basically the same as `spatie/laravel-translatable`, except I renamed a few things.

Testing
-------

```bash
composer test
```

Credits
-------

Obviously, all credit goes to Spatie for actually writing this package.

- [Freek Van der Herten](https://github.com/freekmurze)
- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [Mohamed Said](https://github.com/themsaid)
- [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable)

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
