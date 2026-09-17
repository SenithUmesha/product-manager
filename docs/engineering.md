# Engineering notes — Product Manager

Product Manager is a small 2022 Laravel 9 CRUD project. Its size is useful: there is just enough architecture to see the boundaries clearly without hiding them behind a large domain.

This document describes the current public snapshot after a focused cleanup. It does not pretend the project was designed as a production catalog or inventory platform.

## 1. Request flow

```text
browser
   │
   │ authenticated request
   ▼
routes/web.php
   │
   ▼
DashboardController
   │
   ▼
DashboardFacade
   │
   ▼
DashboardService
   │
   ├── Eloquent / products table
   └── public storage / images
```

There are three main layers involved in product behavior:

- **controller** — HTTP validation, response/redirect decisions
- **facade** — Laravel facade proxy for the service
- **service** — product persistence and image lifecycle

For a tiny application this is more layering than strictly necessary, but that makes the repository a useful architecture experiment.

## 2. Authentication boundary

Laravel Jetstream, Sanctum and Livewire are installed in the project.

The product routes now use the standard Jetstream-style middleware group:

```php
[
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
]
```

The original dashboard routes were not wrapped in authentication middleware even though the project had Jetstream scaffolding and a logout control in the navigation.

The cleanup makes that intent explicit: product management belongs to authenticated users.

## 3. HTTP semantics

The original version used `GET` routes for delete, status changes and updates.

That causes several problems:

- crawlers/prefetchers can accidentally trigger state changes
- URLs no longer communicate whether an operation is read-only
- browser caches and intermediary tooling make assumptions about GET safety
- CSRF protection is less naturally applied to the mutation

The current routes are:

```text
GET     /dashboard
POST    /dashboard/products
GET     /dashboard/products/edit
PATCH   /dashboard/products/{product_id}
PATCH   /dashboard/products/{product_id}/status
DELETE  /dashboard/products/{product_id}
```

The edit endpoint is still GET because it only returns the Blade fragment used by the modal.

## 4. Validation boundary

The controller now validates data before it reaches the service.

Create:

```text
name   required string <= 255
price  required numeric >= 0 <= 9,999,999.99
image  required image <= 4 MB
```

Update:

```text
name   required string <= 255
price  required numeric >= 0 <= 9,999,999.99
```

That matters because the original service used `$request->all()` and passed request-shaped data directly into the model.

Even with Eloquent `$fillable`, a better boundary is:

```text
untrusted HTTP request
        │
        ▼
validated array
        │
        ▼
application/service layer
```

A larger version would move these rule sets into Laravel Form Request classes.

## 5. Product model

The database migration defines a deliberately small model:

```text
products
├── id
├── name
├── image
├── price decimal(9,2)
├── status default "inactive"
├── created_at
└── updated_at
```

`App\Models\Products` exposes these fillable fields:

```text
name
image
price
status
```

The class name is plural, which is unconventional Laravel naming (`Product` would be more idiomatic), but Eloquent still resolves the expected `products` table. I kept it to avoid turning a cleanup into a broad rename migration.

## 6. Image lifecycle

Product creation has two pieces of state:

```text
filesystem image
+ database product row
```

The current service uses Laravel's public disk:

```php
$path = $image->store('images', 'public');
```

That lets Laravel generate the filename rather than building one from a timestamp plus the user's original name.

The public path saved to the database becomes:

```text
/storage/images/<generated file>
```

and `php artisan storage:link` exposes the storage disk under `public/storage`.

### deletion

Deleting a product now also cleans up its image:

```text
find product
    │
    ├── /storage/... -> storage-relative path
    ├── Storage::disk('public')->delete(...)
    └── delete product row
```

The old implementation deleted only the Eloquent record, leaving an orphaned file behind.

### next improvement

Creation is still not a cross-resource transaction. If the DB insert failed after the file was stored, the uploaded image could remain orphaned.

A hardened implementation would catch the persistence failure and delete the just-uploaded file, or wrap the lifecycle in a dedicated application operation with compensating cleanup.

## 7. Status transition

The database default is `inactive`.

The original `status()` method could only set:

```text
inactive -> active
```

It could never move a product back.

The current method treats status as a small two-state transition:

```text
active   -> inactive
inactive -> active
```

For this project a string is enough. A newer PHP/Laravel version could use an enum or model cast so unsupported status values cannot enter the application accidentally.

## 8. Facade + service experiment

`DashboardFacade` extends Laravel's base `Facade` and resolves `DashboardService` from the container.

That gives the controller calls like:

```php
DashboardFacade::all();
DashboardFacade::store(...);
DashboardFacade::delete(...);
```

instead of constructing the service directly.

This is useful for learning how Laravel facades proxy a container-resolved dependency, but it is arguably not earning much in such a small app.

If I were writing this today I would likely prefer constructor injection:

```text
DashboardController
       │
       └── ProductService
```

That makes the dependency explicit and is straightforward to replace in tests.

The takeaway is not “facades are bad”; it is that architecture layers should have a job beyond making the call chain longer.

## 9. Blade dashboard

The dashboard is server-rendered Blade with Bootstrap 5.

Create, status and delete actions are ordinary HTML forms, which means Laravel's CSRF token and method spoofing work naturally:

```text
POST + @csrf
PATCH + @method('PATCH')
DELETE + @method('DELETE')
```

The UI also surfaces:

- validation errors
- success flash messages
- empty-state copy
- product count
- active/inactive badges
- confirmation before delete

These are small details, but they make a CRUD tool feel like an application rather than a form next to a table.

## 10. Edit modal

Editing keeps one bit of the original jQuery approach.

```text
click edit
   │
   ▼
GET /dashboard/products/edit?product_id=...
   │
   ▼
controller validates ID
   │
   ▼
render edit.blade.php fragment
   │
   ▼
jQuery inserts fragment into Bootstrap modal
```

The submitted edit itself now uses `PATCH`, so the AJAX GET is only responsible for loading UI.

A modern alternative could use Livewire — already present in this project's dependencies — and keep the modal state entirely server-reactive without a manual jQuery fragment request.

## 11. Environment and local storage

`.env.example` now defaults to:

```text
APP_NAME="Product Manager"
DB_DATABASE=product_manager
FILESYSTEM_DISK=public
```

The actual service also explicitly asks for the `public` disk, so product media is unambiguous.

The expected local setup is:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run dev
php artisan serve
```

No credentials are committed in the repository.

## 12. Things I intentionally did not turn into a rewrite

This cleanup does not add features just to make the repo look bigger.

There is still no:

- SKU/inventory-count model
- category system
- search/pagination
- audit log
- product image replacement flow
- role/permission system beyond authenticated access
- API/mobile client
- queue processing
- dedicated product policy
- meaningful feature-test suite around the dashboard

Those would make sense in a real catalog/admin product, but this repo is more useful as a focused Laravel learning snapshot.

## 13. Testing opportunities

The strongest next tests would be feature tests rather than unit tests of the facade itself.

```text
guest cannot access /dashboard
authenticated user can create valid product
invalid image is rejected
new product defaults inactive
status endpoint toggles both directions
update changes only name/price
delete removes row and public image
unknown product returns 404
```

Storage can be isolated with Laravel's fake disk:

```php
Storage::fake('public');
```

That would make the image lifecycle particularly easy to verify.

## 14. If rebuilt today

For roughly the same feature set I would aim for fewer concepts, stronger framework conventions and better tests:

```text
routes + auth middleware
        │
route-model binding
        │
Form Requests
        │
ProductController
        │
ProductService (only where file lifecycle earns it)
        │
Eloquent + Storage
```

I would also likely:

- rename `Products` -> `Product`
- use resourceful routes/controller methods
- model status as an enum/cast
- add pagination/search
- allow safe image replacement
- use Livewire for the modal/status interactions
- add feature tests for every mutation
- add authorization policies if multiple user roles appeared

The original project was an experiment in adding structure to CRUD. The cleaner version makes the same lesson a bit clearer: **use Laravel's conventions first, then add abstractions where the domain actually needs them.**
