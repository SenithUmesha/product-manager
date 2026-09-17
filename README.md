# Product Manager 🧃

> a tiny 2022 Laravel dashboard for adding products, uploading images, changing status and learning where “just CRUD” starts turning into app structure.

This is a small Laravel 9 project I built while experimenting with **Eloquent, Blade, Jetstream authentication, file storage and a service/facade layer**.

It is not trying to be Shopify. That is kind of the point.

The app has one focused job: give a signed-in user a simple dashboard where products can be created, edited, activated/deactivated and deleted.

## what it does

```text
sign in
   │
   ▼
product dashboard
   │
   ├── add product + image
   ├── edit name / price
   ├── active ↔ inactive
   └── delete product + stored image
```

Each product currently has:

```text
name
image
price
status
created_at
updated_at
```

New products start as `inactive` and can be toggled active from the dashboard.

## stack

`PHP 8` · `Laravel 9` · `Eloquent` · `Blade`

`Laravel Jetstream` · `Sanctum` · `Livewire`

`Bootstrap 5` · `jQuery` · `MySQL` · `Laravel Storage`

The repository also contains the Laravel Vite/Tailwind/Alpine tooling that came with the project setup, although the actual product dashboard uses Bootstrap and a small amount of jQuery.

## the architecture experiment

The part I was really playing with here was putting product behavior somewhere other than the controller.

```text
HTTP request
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
    ├── Eloquent Products model
    └── public storage disk
```

The controller now owns HTTP concerns such as request validation and redirects. The service owns the product/storage lifecycle.

For the deeper walkthrough, including why the facade is arguably unnecessary for a project this small, see [`docs/engineering.md`](docs/engineering.md).

## public-repo cleanup

The original version was intentionally minimal and had a few classic early CRUD shortcuts. I cleaned those up while keeping the project recognizable:

- dashboard routes now require Jetstream authentication
- create/update requests are validated server-side
- delete/update/status mutations use `DELETE` / `PATCH` instead of `GET`
- product lookups use `findOrFail()`
- image files use Laravel's generated storage names rather than concatenating original filenames
- deleting a product also deletes its stored image
- status now actually toggles `active ↔ inactive`
- mass updates only use validated fields
- dashboard forms show validation and success feedback
- `.env.example` now matches the project name/database/storage setup

This is still a small 2022 learning app, not a claim of production inventory architecture.

## run it locally

Requirements:

- PHP 8+
- Composer
- MySQL / MariaDB
- Node.js + npm

Install dependencies:

```bash
composer install
npm install
```

Create your local environment:

```bash
cp .env.example .env
php artisan key:generate
```

Create a MySQL database called `product_manager` (or change `DB_DATABASE`), then run:

```bash
php artisan migrate
php artisan storage:link
```

Build frontend assets:

```bash
npm run dev
```

And start Laravel:

```bash
php artisan serve
```

Jetstream provides the account flow. Register/sign in, then the app redirects to `/dashboard`.

## image lifecycle

Images are stored on Laravel's `public` disk:

```text
upload
  │
  ▼
storage/app/public/images/<generated name>
  │
  ▼
public/storage/... via artisan storage:link
```

The database stores the `/storage/...` path used by Blade.

When a product is deleted, the service strips that public prefix and removes the corresponding file from the storage disk before deleting the database record.

## HTTP flow

The cleaned-up product endpoints are intentionally boring in the good way:

```text
GET     /dashboard
POST    /dashboard/products
GET     /dashboard/products/edit?product_id=...
PATCH   /dashboard/products/{id}
PATCH   /dashboard/products/{id}/status
DELETE  /dashboard/products/{id}
```

All of them sit behind the Jetstream authenticated/verified middleware group.

## if i rebuilt it now

For a project this size I would probably make the architecture **simpler**, not more elaborate.

The facade layer is useful as an experiment, but a modern version could inject a `ProductService` directly into the controller, use route-model binding, dedicated Form Requests, PHP enums/casts for status, pagination, image replacement, feature tests and probably Livewire or a small SPA-style interaction instead of jQuery-loaded modal HTML.

The useful lesson from this repo is that extra layers only earn their place when they create a real boundary.

## project status

Historical side project / Laravel learning experiment.

Kept public because it is a compact snapshot of me poking at backend structure before most of my GitHub became mobile apps.
