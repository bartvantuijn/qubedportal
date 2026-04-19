# AGENTS.md

Instructions for AI coding assistants working on **Qubed Portal**. These rules
are tool-agnostic: any assistant (Claude, Codex, Cursor, Copilot, etc.) should
follow them.

Qubed Portal is an internal Laravel + Filament application for managing
QubedWP clients, licenses, subscriptions and expenses. The guiding principle
is **simplicity, pragmatism and structure**. Favour readable code over clever
code, and mirror existing patterns before introducing new ones.

---

## 1. Tech stack

- PHP `^8.2`
- Laravel `^11.31`
- Filament `^3.2` (admin panel, forms, tables, widgets)
- Laravel Sanctum `^4.0` (API tokens)
- Tailwind CSS `^3.4` + Vite `^5.0` (frontend build only — no SPA framework)
- SQLite by default (configurable via `.env`)
- PHPUnit `^11.0` for tests
- Laravel Pint + Rector for code style and quality

There is **no Livewire component layer, no Inertia, no Vue/React**. All UI
lives inside Filament resources, pages and widgets. Blade is only used for the
thin layout shell (favicon, assets).

---

## 2. Project layout

```
app/
  Filament/
    Resources/         # CRUD: ClientResource, LicenseResource,
                       # ProductResource, SubscriptionResource, ExpenseResource
    Widgets/           # Dashboard widgets (StatsOverview, DateWidget)
  Http/Controllers/    # Only API controllers live here
  Models/              # Eloquent models (Client, License, Product,
                       # Subscription, Expense, User)
  Providers/
    AppServiceProvider.php
    Filament/AdminPanelProvider.php
database/
  migrations/          # Timestamped Laravel 11 migrations
  factories/           # Only UserFactory for now
  seeders/             # DatabaseSeeder creates the test user
routes/
  web.php              # Filament handles `/`
  api.php              # POST /api/license/validate
resources/
  css/ js/ views/      # Minimal — Filament owns the UI
tests/
  Feature/ Unit/
```

When adding a feature, follow this layout. Do **not** introduce a new
top-level directory unless there is no existing place for the code.

---

## 3. Patterns to follow

### Models

- Keep models thin: relationships + `casts()`. No business logic, no scopes
  unless a feature genuinely needs them.
- `Model::unguard()` is set globally in `AppServiceProvider::boot()`, so do
  **not** add `$fillable` / `$guarded` to individual models.
- Cast date columns via a `protected function casts(): array` method.
- Relationships always use return type hints (`BelongsTo`, `HasMany`, …).

Example shape:

```php
class Subscription extends Model
{
    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
```

### Migrations

- One migration per table. Use the Laravel 11 anonymous class style.
- Foreign keys: `$table->foreignId('client_id')->index();` — no cascade rules
  unless the feature specifically requires them.
- Money columns: `$table->decimal('price');` (Laravel default precision).
- Enums stay inside the migration (`$table->enum('frequency', [...])`).
- Always write a matching `down()` with `Schema::dropIfExists(...)`.

### Filament resources

Every resource follows the same skeleton. Use `SubscriptionResource` as the
canonical reference.

1. `protected static ?string $model = ...;`
2. `protected static ?string $navigationIcon = 'heroicon-o-...';`
3. `getModelLabel()` / `getPluralModelLabel()` return `__('...')` strings.
4. `getGlobalSearchResultTitle()` and `getGloballySearchableAttributes()` when
   the record has human-readable identifiers.
5. `form()` delegates to a `public static function getFormSchema(): array`.
6. `table()` delegates to a `public static function getTableColumns(): array`,
   adds `EditAction`, a `DeleteBulkAction` bulk group, and an
   `emptyStateHeading(__('No ...'))`.
7. `getPages()` wires `index`, `create`, `edit` to resource pages under
   `app/Filament/Resources/<Name>Resource/Pages/`.

Page classes are intentionally minimal:

- `List<Name>` adds a `CreateAction` header action.
- `Create<Name>` sets `protected static bool $canCreateAnother = false;`.
- `Edit<Name>` adds a `DeleteAction` header action.

Form conventions:

- Every field has an `->label(__('...'))`.
- Selects use `->relationship(...)`, `->searchable()`, `->preload()`.
- Currency inputs use `->prefixIcon('heroicon-o-currency-euro')` and
  `->mask(RawJs::make('$money($input)'))`.
- Date pickers use `->native(false)` and `->live(onBlur: true)` when they
  constrain each other via `minDate` / `maxDate`.
- Long-form fields use `RichEditor` with `->columnSpan('full')`.

Table conventions:

- Money columns use `->money('EUR')`.
- Recurring money columns add a `Summarizer` that normalises to a yearly total
  via a `match ($row->frequency) { ... }` block. Keep the ladder consistent
  across the app: `daily => * 365`, `monthly => * 12`, `yearly => * 1`,
  `default => 0`.
- Enum columns use `->formatStateUsing(fn (string $state): string => __(ucfirst($state)))`.

### Dashboard widgets

- Widgets live in `app/Filament/Widgets/` and are auto-discovered.
- Use `protected static ?int $sort` to order them. `DateWidget` is 2,
  `StatsOverview` is 3.
- `StatsOverview` is the single source of dashboard KPIs. When a new model
  affects revenue, cost or profit, extend `StatsOverview` rather than creating
  a parallel widget.
- Money is formatted by the private `money()` helper on the widget — reuse it,
  do not re-implement formatting.

### Routes

- Web routes are empty by design — Filament serves the UI at `/`.
- API routes live in `routes/api.php` and validate input inline with
  `$request->validate([...])`. Controllers return plain JSON arrays.

### Authorization

- There is no role/permission package. `User::canAccessPanel()` gates the
  admin panel. Do not add Spatie Permission or policies unless the feature
  requires it.

### Translations

- All user-facing strings go through `__('...')`. Keys are the English
  sentence, not dot-paths. Don't hardcode strings in resources or widgets.

---

## 4. Code style

- Run `composer cs` before committing. It executes `rector` then `pint` with
  the project configs (`rector.php`, `pint.json`).
- Pint preset is `laravel` with these additions: `blank_line_before_statement`,
  `concat_space: one`, `method_argument_space`, `single_trait_insert_per_statement`,
  `types_spaces: single`.
- Use short arrow functions (`fn () =>`) where they fit on one line.
- Import classes with `use` statements; don't use fully-qualified names inline.
- Prefer `match` over `switch`.

---

## 5. Testing

- Framework is **PHPUnit**, not Pest. New tests extend `Tests\TestCase`.
- Place feature tests in `tests/Feature`, unit tests in `tests/Unit`.
- Run the suite with `php artisan test` (or `vendor/bin/phpunit`).
- There is no CI gate today, but tests must pass locally before pushing.

---

## 6. Git workflow

### Branches

- `master` is the trunk.
- Feature branches use short, lowercase, hyphenated names describing the
  change: `add-expenses`, `fix-license-verification`, `tweak-dashboard-stats`.
- AI-assistant branches follow `claude/<topic>-<shortid>` (or the assistant's
  own prefix). Always develop on the branch the task specifies and push
  **only** to that branch unless told otherwise.

### Commits

- Imperative mood, short (≤ 70 chars), no trailing period, capitalised verb:
  `Add expenses resource`, `Fix code style`, `Update README.md`,
  `Improve schema consistency`.
- Keep commits focused. A schema change, a resource change and a widget
  change belong in one commit **only** when they form a single feature; split
  unrelated cleanups into their own commits.
- Do not amend or force-push shared branches.

### Pull requests

- Only open a PR when the user explicitly asks for one.
- PR titles follow the same rules as commit messages.
- PR descriptions cover: what changed, why, and how to verify.

---

## 7. How to add a feature (checklist)

Use this every time. It keeps new work consistent with what's already there.

1. **Read first.** Open the closest existing feature end-to-end (model →
   migration → resource → pages → widget impact → tests) before writing code.
2. **Model.** Create `app/Models/<Name>.php` with relationships and `casts()`.
3. **Migration.** Add a timestamped migration under `database/migrations/`.
   Run `php artisan migrate` locally to confirm it applies.
4. **Related models.** Update `hasMany` / `belongsTo` on existing models that
   the new one relates to.
5. **Filament resource.** Create `app/Filament/Resources/<Name>Resource.php`
   following the skeleton in section 3. Add matching Pages classes under
   `app/Filament/Resources/<Name>Resource/Pages/`.
6. **Dashboard.** If the feature affects KPIs, extend `StatsOverview` rather
   than adding a parallel widget.
7. **Translations.** Wrap every new user-facing string in `__()`.
8. **Style + quality.** Run `composer cs`.
9. **Tests.** Add or update tests under `tests/` when the feature has
   non-trivial logic (calculations, API contracts, authorization).
10. **Commit + push.** One focused commit (or a tight series), push to the
    designated branch.

---

## 8. Things to avoid

- Don't add packages when Laravel or Filament already ship the capability.
- Don't introduce new architectural layers (services, repositories, DTOs) for
  CRUD features. Filament resources + Eloquent models are enough.
- Don't duplicate the frequency-to-yearly `match` ladder — reuse the shape
  used in `SubscriptionResource` and `StatsOverview` so the numbers stay
  consistent.
- Don't rename or move existing files "for consistency" while implementing an
  unrelated feature.
- Don't add `$fillable` / `$guarded`, policies, role systems, or middleware
  unless the task explicitly calls for them.
- Don't skip `composer cs` — style drift shows up in review noise.
