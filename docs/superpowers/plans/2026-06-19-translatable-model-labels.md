# Translatable Filament Model Labels — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a public Composer package that makes Filament Resource model labels (singular + plural, and therefore navigation + table heading) resolve through Laravel's `__()` translation, keyed off the auto-derived model name.

**Architecture:** A trait `TranslatesFilamentModelLabels` overrides `getModelLabel()` / `getPluralModelLabel()` to wrap the humanised model name in `__()`. An optional abstract base class `TranslatableFilamentResource` applies the trait for inheritance-based consumers. A minimal ServiceProvider enables Laravel package auto-discovery. Tested with Pest + Orchestra Testbench against a real `Filament\Resources\Resource`.

**Tech Stack:** PHP 8.3+, Filament v5, Laravel, Pest v4, Orchestra Testbench, Laravel Pint.

## Global Constraints

- Package name: `madbox-99/filament-translatable-model-labels`
- PSR-4 namespace: `MadBox99\FilamentTranslatableModelLabels\` → `src/`
- Test namespace: `MadBox99\FilamentTranslatableModelLabels\Tests\` → `tests/`
- PHP requirement: `^8.3`
- Filament requirement: `filament/filament: ^5.0`
- Trait name: `TranslatesFilamentModelLabels` (in `Concerns/`)
- Base class name: `TranslatableFilamentResource`
- License: MIT
- Visibility: public (Packagist)
- Use the existing Filament helpers `Filament\Support\get_model_label()` and `Filament\Support\locale_has_pluralization()` — do not reimplement them.
- Translation keys are flat/global (`__('issue')`, `__('issues')`); multi-word models use spaced keys (`__('blog post')`).
- Run `vendor/bin/pint` before every commit; commit only with a green `vendor/bin/pest`.

---

### Task 1: Package scaffold, ServiceProvider, and green empty test suite

**Files:**
- Create: `composer.json`
- Create: `.gitignore`
- Create: `phpunit.xml.dist`
- Create: `pint.json`
- Create: `src/FilamentTranslatableModelLabelsServiceProvider.php`
- Create: `tests/TestCase.php`
- Create: `tests/Pest.php`
- Create: `tests/fixtures/lang/hu.json`
- Create: `tests/Feature/SanityTest.php`

**Interfaces:**
- Consumes: nothing (first task).
- Produces:
  - `MadBox99\FilamentTranslatableModelLabels\FilamentTranslatableModelLabelsServiceProvider` — Laravel ServiceProvider, no public API.
  - `MadBox99\FilamentTranslatableModelLabels\Tests\TestCase` — base test case; registers the package provider and adds `tests/fixtures/lang` as a JSON translation path so `__('issue')` resolves from `hu.json` when the locale is `hu`.

- [ ] **Step 1: Create `composer.json`**

```json
{
    "name": "madbox-99/filament-translatable-model-labels",
    "description": "Resolve Filament Resource model labels (singular and plural) through Laravel translations.",
    "keywords": ["filament", "laravel", "translation", "i18n", "labels"],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "filament/filament": "^5.0"
    },
    "require-dev": {
        "laravel/pint": "^1.0",
        "orchestra/testbench": "^10.0|^11.0",
        "pestphp/pest": "^4.0"
    },
    "autoload": {
        "psr-4": {
            "MadBox99\\FilamentTranslatableModelLabels\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "MadBox99\\FilamentTranslatableModelLabels\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "MadBox99\\FilamentTranslatableModelLabels\\FilamentTranslatableModelLabelsServiceProvider"
            ]
        }
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    },
    "prefer-stable": true
}
```

- [ ] **Step 2: Create `.gitignore`**

```gitignore
/vendor/
composer.lock
/.phpunit.cache/
.phpunit.result.cache
/build/
.DS_Store
```

- [ ] **Step 3: Create `pint.json`** (Laravel preset)

```json
{
    "preset": "laravel"
}
```

- [ ] **Step 4: Create `phpunit.xml.dist`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         backupGlobals="false"
         colors="true"
         processIsolation="false"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

- [ ] **Step 5: Create the ServiceProvider `src/FilamentTranslatableModelLabelsServiceProvider.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels;

use Illuminate\Support\ServiceProvider;

class FilamentTranslatableModelLabelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
```

- [ ] **Step 6: Create the translation fixture `tests/fixtures/lang/hu.json`**

```json
{
    "issue": "probléma",
    "issues": "problémák",
    "blog post": "blogbejegyzés",
    "blog posts": "blogbejegyzések",
    "comment": "hozzászólás",
    "comments": "hozzászólások"
}
```

- [ ] **Step 7: Create the base test case `tests/TestCase.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests;

use MadBox99\FilamentTranslatableModelLabels\FilamentTranslatableModelLabelsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentTranslatableModelLabelsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['translator']->addJsonPath(__DIR__ . '/fixtures/lang');
    }
}
```

- [ ] **Step 8: Create `tests/Pest.php`**

```php
<?php

use MadBox99\FilamentTranslatableModelLabels\Tests\TestCase;

uses(TestCase::class)->in('Feature');
```

- [ ] **Step 9: Create a sanity test `tests/Feature/SanityTest.php`**

```php
<?php

it('boots the testbench application', function () {
    expect(app()->bound('translator'))->toBeTrue();
});
```

- [ ] **Step 10: Install dependencies**

Run: `composer update`
Expected: resolves and installs `filament/filament`, `orchestra/testbench`, `pestphp/pest`, `laravel/pint` with no errors. (If `orchestra/testbench` fails to resolve against the installed Filament/Laravel versions, widen the `require-dev` constraint to the version Composer reports as compatible, then re-run.)

- [ ] **Step 11: Run the suite to verify it is green**

Run: `vendor/bin/pest`
Expected: PASS — 1 passed.

- [ ] **Step 12: Format and commit**

```bash
vendor/bin/pint
git add -A
git commit -m "feat: package scaffold, service provider, and test harness"
```

---

### Task 2: `TranslatesFilamentModelLabels` trait

**Files:**
- Create: `src/Concerns/TranslatesFilamentModelLabels.php`
- Create: `tests/Fixtures/Models/Issue.php`
- Create: `tests/Fixtures/Models/BlogPost.php`
- Create: `tests/Fixtures/Resources/IssueResource.php`
- Create: `tests/Fixtures/Resources/IssueResourceWithExplicitLabel.php`
- Create: `tests/Fixtures/Resources/BlogPostResource.php`
- Test: `tests/Feature/TranslatesFilamentModelLabelsTest.php`

**Interfaces:**
- Consumes: `MadBox99\FilamentTranslatableModelLabels\Tests\TestCase` (Task 1); the JSON translations in `tests/fixtures/lang/hu.json` (Task 1).
- Produces:
  - `MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels` — a trait intended to be used inside a class extending `Filament\Resources\Resource`. Overrides two static methods:
    - `public static function getModelLabel(): string`
    - `public static function getPluralModelLabel(): string`

- [ ] **Step 1: Create the test models**

`tests/Fixtures/Models/Issue.php`:

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    //
}
```

`tests/Fixtures/Models/BlogPost.php`:

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    //
}
```

- [ ] **Step 2: Create the test resources**

`tests/Fixtures/Resources/IssueResource.php`:

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\Issue;

class IssueResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = Issue::class;
}
```

`tests/Fixtures/Resources/IssueResourceWithExplicitLabel.php`:

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\Issue;

class IssueResourceWithExplicitLabel extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = Issue::class;

    protected static ?string $modelLabel = 'Custom singular';

    protected static ?string $pluralModelLabel = 'Custom plural';
}
```

`tests/Fixtures/Resources/BlogPostResource.php`:

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\BlogPost;

class BlogPostResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = BlogPost::class;
}
```

- [ ] **Step 3: Write the failing test `tests/Feature/TranslatesFilamentModelLabelsTest.php`**

```php
<?php

use Illuminate\Support\Facades\App;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\BlogPostResource;
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
```

- [ ] **Step 4: Run the test to verify it fails**

Run: `vendor/bin/pest --filter=TranslatesFilamentModelLabels`
Expected: FAIL — class `MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels` not found.

- [ ] **Step 5: Implement the trait `src/Concerns/TranslatesFilamentModelLabels.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Concerns;

use Illuminate\Support\Str;

use function Filament\Support\get_model_label;
use function Filament\Support\locale_has_pluralization;

/**
 * Resolves a Filament Resource's model labels through Laravel's `__()` translation,
 * keyed off the auto-derived model name. Intended for use inside a class that
 * extends `Filament\Resources\Resource`.
 */
trait TranslatesFilamentModelLabels
{
    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __(get_model_label(static::getModel()));
    }

    public static function getPluralModelLabel(): string
    {
        if (filled($label = static::$pluralModelLabel)) {
            return $label;
        }

        $base = static::$modelLabel ?? get_model_label(static::getModel());

        return __(locale_has_pluralization() ? Str::plural($base) : $base);
    }
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `vendor/bin/pest --filter=TranslatesFilamentModelLabels`
Expected: PASS — 5 passed.

- [ ] **Step 7: Run the whole suite**

Run: `vendor/bin/pest`
Expected: PASS — all green.

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint
git add -A
git commit -m "feat: add TranslatesFilamentModelLabels trait"
```

---

### Task 3: `TranslatableFilamentResource` base class

**Files:**
- Create: `src/TranslatableFilamentResource.php`
- Create: `tests/Fixtures/Models/Comment.php`
- Create: `tests/Fixtures/Resources/CommentResource.php`
- Test: `tests/Feature/TranslatableFilamentResourceTest.php`

**Interfaces:**
- Consumes: `MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels` (Task 2); `MadBox99\FilamentTranslatableModelLabels\Tests\TestCase` (Task 1).
- Produces:
  - `MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource` — an abstract class extending `Filament\Resources\Resource` that applies `TranslatesFilamentModelLabels`. Consumers extend it instead of adding the trait by hand.

- [ ] **Step 1: Create the test model `tests/Fixtures/Models/Comment.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
}
```

- [ ] **Step 2: Create the test resource `tests/Fixtures/Resources/CommentResource.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\Comment;

class CommentResource extends TranslatableFilamentResource
{
    protected static ?string $model = Comment::class;
}
```

- [ ] **Step 3: Write the failing test `tests/Feature/TranslatableFilamentResourceTest.php`**

```php
<?php

use Illuminate\Support\Facades\App;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\CommentResource;

it('translates labels for a resource extending the base class', function () {
    App::setLocale('hu');

    expect(CommentResource::getModelLabel())->toBe('hozzászólás');
    expect(CommentResource::getPluralModelLabel())->toBe('hozzászólások');
});

it('falls back for the base class when no translation exists', function () {
    App::setLocale('en');

    expect(CommentResource::getModelLabel())->toBe('comment');
    expect(CommentResource::getPluralModelLabel())->toBe('comments');
});
```

- [ ] **Step 4: Run the test to verify it fails**

Run: `vendor/bin/pest --filter=TranslatableFilamentResource`
Expected: FAIL — class `MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource` not found.

- [ ] **Step 5: Implement `src/TranslatableFilamentResource.php`**

```php
<?php

namespace MadBox99\FilamentTranslatableModelLabels;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;

abstract class TranslatableFilamentResource extends Resource
{
    use TranslatesFilamentModelLabels;
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `vendor/bin/pest --filter=TranslatableFilamentResource`
Expected: PASS — 2 passed.

- [ ] **Step 7: Run the whole suite**

Run: `vendor/bin/pest`
Expected: PASS — all green.

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint
git add -A
git commit -m "feat: add TranslatableFilamentResource base class"
```

---

### Task 4: README and LICENSE

**Files:**
- Create: `README.md`
- Create: `LICENSE`

**Interfaces:**
- Consumes: the public API from Tasks 2–3 (the trait and base class names) for the usage docs.
- Produces: documentation only; no code API.

- [ ] **Step 1: Create `LICENSE` (MIT)**

```text
MIT License

Copyright (c) 2026 MadBox-99

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

- [ ] **Step 2: Create `README.md`**

````markdown
# Filament Translatable Model Labels

Resolve Filament Resource model labels (singular **and** plural) — and therefore
the navigation item and table heading — through Laravel's `__()` translation,
keyed off the auto-derived model name. Define the translation once; every
Resource picks it up without per-Resource overrides.

## Installation

```bash
composer require madbox-99/filament-translatable-model-labels
```

## Usage

Apply the trait to a shared base Resource so it is never repeated:

```php
use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;

abstract class BaseResource extends Resource
{
    use TranslatesFilamentModelLabels;
}
```

Or extend the provided base class:

```php
use MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource;

class IssueResource extends TranslatableFilamentResource
{
    protected static ?string $model = \App\Models\Issue::class;
}
```

Add the translations to your app's `lang/hu.json`:

```json
{
    "issue": "probléma",
    "issues": "problémák"
}
```

Result: the create button reads "Új probléma", and the navigation item and table
heading read "Problémák".

## How it works

- The singular label is `__(<humanised model name>)` — e.g. `Issue` → `__('issue')`.
- The plural label is `__(Str::plural(<humanised model name>))` — e.g. `__('issues')`.
- An explicit `$modelLabel` / `$pluralModelLabel` on the Resource still wins.
- If no translation exists, `__()` returns the key unchanged, so untranslated
  locales behave exactly like stock Filament.

### Multi-word models

The key is the humanised, lower-cased model name with spaces, e.g. `BlogPost`
becomes `__('blog post')` / `__('blog posts')`:

```json
{
    "blog post": "blogbejegyzés",
    "blog posts": "blogbejegyzések"
}
```

## Testing

```bash
composer test
```

## License

MIT.
````

- [ ] **Step 3: Add a `test` script to `composer.json`**

Add this `scripts` block to `composer.json` (top level, after `config`):

```json
    "scripts": {
        "test": "vendor/bin/pest",
        "format": "vendor/bin/pint"
    },
```

- [ ] **Step 4: Verify composer.json is still valid**

Run: `composer validate --strict`
Expected: `./composer.json is valid`.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "docs: add README and LICENSE"
```

---

### Task 5: Continuous integration (Pest + Pint)

**Files:**
- Create: `.github/workflows/tests.yml`

**Interfaces:**
- Consumes: the `composer test` script (Task 4) and `pint.json` (Task 1).
- Produces: a GitHub Actions workflow; no code API.

- [ ] **Step 1: Create `.github/workflows/tests.yml`**

```yaml
name: tests

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.3', '8.4']

    name: PHP ${{ matrix.php }}
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: none

      - name: Install dependencies
        run: composer update --prefer-dist --no-interaction --no-progress

      - name: Check code style
        run: vendor/bin/pint --test

      - name: Run tests
        run: vendor/bin/pest
```

- [ ] **Step 2: Verify the workflow file is valid YAML**

Run: `php -r "var_dump(yaml_parse_file('.github/workflows/tests.yml') !== false);"` if the `yaml` extension is available; otherwise visually confirm indentation is two-space and consistent.
Expected: no parse error (or a clean visual check).

- [ ] **Step 3: Commit and push**

```bash
git add -A
git commit -m "ci: run Pest and Pint on push and pull request"
git push origin main
```

- [ ] **Step 4: Confirm CI is green**

Run: `gh run watch` (or `gh run list --limit 1`)
Expected: the latest run for `main` completes with conclusion `success`.

---

### Task 6: Publish to Packagist

**Files:** none (release + external service).

**Interfaces:**
- Consumes: the full, green repository from Tasks 1–5.
- Produces: a tagged release `v1.0.0` and a public Packagist listing.

> **Note:** Steps 2–4 act on external services (GitHub repo visibility, Packagist). These should be performed by the maintainer; the implementing agent should pause and hand these off rather than executing them automatically.

- [ ] **Step 1: Tag the release**

```bash
git tag v1.0.0
git push origin v1.0.0
```

- [ ] **Step 2: Make the GitHub repository public** (maintainer action)

```bash
gh repo edit MadBox-99/filament-translatable-model-labels --visibility public --accept-visibility-change-consequences
```

- [ ] **Step 3: Submit to Packagist** (maintainer action)

Open https://packagist.org/packages/submit and submit
`https://github.com/MadBox-99/filament-translatable-model-labels`.

- [ ] **Step 4: Enable the Packagist auto-update webhook** (maintainer action)

Follow the "How to set up the GitHub Hook" instructions Packagist shows after
submission, so future tags update the package automatically.

- [ ] **Step 5: Verify installability**

In a separate throwaway directory:
Run: `composer require madbox-99/filament-translatable-model-labels`
Expected: installs `v1.0.0` without errors.

---

## Self-Review

**Spec coverage:**
- Trait `TranslatesFilamentModelLabels` (singular + plural via `__()`, explicit-override-wins, fallback) → Task 2. ✓
- Optional base class `TranslatableFilamentResource` → Task 3. ✓
- Minimal ServiceProvider + auto-discovery → Task 1. ✓
- composer.json / namespace / deps → Task 1. ✓
- Pest + Testbench tests (translate, plural, fallback, explicit-override, table/multi-word) → Tasks 2–3. ✓ (Navigation/table heading derive from these label methods in Filament, so they are covered transitively; the dedicated multi-word and plural cases exercise the key derivation.)
- README with hu.json + multi-word docs → Task 4. ✓
- CI (Pest + Pint) → Task 5. ✓
- Public Packagist publish, tag v1.0.0 → Task 6. ✓
- Use Filament helpers, flat keys, multi-word spaced keys → Task 2 implementation + README. ✓

**Placeholder scan:** No TBD/TODO; every code step contains complete code; commands have expected output. ✓

**Type consistency:** `TranslatesFilamentModelLabels` and `TranslatableFilamentResource` names are used identically across Tasks 2, 3, and 4. Method signatures `getModelLabel(): string` / `getPluralModelLabel(): string` match Filament's `HasLabels` contract and are consistent between the trait (Task 2) and its consumers (Tasks 2–3). ✓

**Known risk noted in-plan:** Task 1 Step 10 covers the one environment-dependent point (testbench version resolution for the installed Filament/Laravel) with an explicit remedy.
