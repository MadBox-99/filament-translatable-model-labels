<?php

use Illuminate\Support\Facades\App;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\BlogPostResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\CommentResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\IssueResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\IssueResourceWithExplicitLabel;

it('translates the singular model label from a json translation', function () {
    App::setLocale('hu');

    expect(IssueResource::getModelLabel())->toBe('probléma');
});

it('translates the plural model label from a json translation', function () {
    App::setLocale('hu');

    expect(IssueResource::getPluralModelLabel())->toBe('problémák');
});

it('falls back to the humanised label when no translation exists', function () {
    App::setLocale('en');

    expect(IssueResource::getModelLabel())->toBe('issue');
    expect(IssueResource::getPluralModelLabel())->toBe('issues');
});

it('lets an explicit model label win over translation', function () {
    App::setLocale('hu');

    expect(IssueResourceWithExplicitLabel::getModelLabel())->toBe('Custom singular');
    expect(IssueResourceWithExplicitLabel::getPluralModelLabel())->toBe('Custom plural');
});

it('translates multi-word model labels using spaced keys', function () {
    App::setLocale('hu');

    expect(BlogPostResource::getModelLabel())->toBe('blogbejegyzés');
    expect(BlogPostResource::getPluralModelLabel())->toBe('blogbejegyzések');
});

/**
 * `key_case` exists for apps whose translation namespace is already capitalised — the case
 * Filament itself produces for field labels, since `->translateLabel()` keys off
 * `Str::headline()`. Without it such an app carries two keys per noun that differ only in case.
 */
it('looks the key up capitalised when key_case is ucfirst', function () {
    config()->set('filament-translatable-model-labels.key_case', 'ucfirst');
    App::setLocale('hu');

    expect(IssueResource::getModelLabel())->toBe('Probléma');
    expect(IssueResource::getPluralModelLabel())->toBe('Problémák');
});

it('capitalises only the first word of a multi-word key', function () {
    config()->set('filament-translatable-model-labels.key_case', 'ucfirst');
    App::setLocale('hu');

    expect(BlogPostResource::getModelLabel())->toBe('Blogbejegyzés');
    expect(BlogPostResource::getPluralModelLabel())->toBe('Blogbejegyzések');
});

/**
 * Only the LOOKUP is capitalised. An untranslated locale must still render exactly like stock
 * Filament — "Create comment", not "Create Comment" — so the fallback stays the lower-cased label
 * Filament derived. `comment` is translated here in lower case only, so the `ucfirst` key misses.
 */
it('falls back to the lower-cased Filament label when the capitalised key is untranslated', function () {
    config()->set('filament-translatable-model-labels.key_case', 'ucfirst');
    App::setLocale('hu');

    expect(CommentResource::getModelLabel())->toBe('comment');
    expect(CommentResource::getPluralModelLabel())->toBe('comments');
});

it('keeps the lower-cased key by default', function () {
    App::setLocale('hu');

    expect(config('filament-translatable-model-labels.key_case'))->toBe('lower');
    expect(IssueResource::getModelLabel())->toBe('probléma');
});
