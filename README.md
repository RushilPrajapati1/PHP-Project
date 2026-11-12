# Ledger

A full-stack expense tracker. Create accounts, record income and expenses, and see each month's totals broken down by category.

**Live demo:** https://php-project-production-j1pkfi.laravel.cloud

Register with any email and password to try it — no email verification is required. The demo sleeps when idle, so the first page load can take a few seconds.

[![tests](https://github.com/RushilPrajapati1/PHP-Project/actions/workflows/tests.yml/badge.svg)](https://github.com/RushilPrajapati1/PHP-Project/actions/workflows/tests.yml)

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | React 19, TypeScript, Inertia 3, Tailwind CSS 4 |
| Database | MySQL 8 |
| Auth | Laravel Fortify (sessions, 2FA, passkeys) for the web app; Laravel Sanctum tokens for the API |
| Testing and quality | Pest, PHPStan (Larastan, level 7), Pint, GitHub Actions |
| Hosting | Laravel Cloud |

## Features

- **Accounts** — checking, savings or credit, each with a running balance.
- **Transactions** — debit or credit, with a category, optional description and date. Paginated, newest first.
- **Monthly summary** — income, spending and net for any month, grouped by category.
- **REST API** — the same data over JSON, authenticated with a bearer token.
- **Per-user data** — users can only see and change their own accounts and transactions.

## How it is built

- **Money is integer cents.** Amounts are stored and calculated as integers (`amount_cents`) and only converted to dollars at the edge, in the API Resources and the UI. No floats are stored.
- **Typed PHP.** Every application file declares `strict_types`. Account and transaction types are backed enums (`App\Enums`), cast on the models.
- **One authorization rule.** `AccountPolicy` decides who owns an account. Transactions are authorized through their parent account, so there is a single place that answers "can this user touch this?"
- **Validation in Form Requests.** The web forms and the API share the same request classes (`app/Http/Requests/Api`). Authorization runs before validation, so a non-owner gets a 403 rather than validation errors.
- **One summary service.** `App\Services\LedgerSummary` groups a month's transactions by category in a single SQL query and returns a typed `MonthlySummary` object. The API and the React pages both call it, so their numbers always match.
- **Inertia instead of a separate SPA.** Controllers return `Inertia::render('accounts/show', [...])` and the matching React page receives that data as typed props. Forms use Inertia's `useForm`, so server-side validation errors appear under the fields.

```
app/
├── Enums/              AccountType, TransactionType
├── Models/             User, Account, Transaction
├── Policies/           AccountPolicy
├── Services/           LedgerSummary
├── Data/               MonthlySummary, CategoryTotal (read-only result objects)
└── Http/
    ├── Controllers/    web (Inertia) controllers, plus Api/ for JSON
    ├── Requests/Api/   Form Requests shared by web and API
    └── Resources/      JSON shaping, cents → dollars
resources/js/
├── pages/accounts/     index.tsx, show.tsx
└── components/ledger/  forms, transactions table, monthly summary
routes/
├── web.php             pages
└── api.php             JSON API
tests/Feature/          Pest tests for the API and the pages
```

## API

All endpoints are under `/api` and need an `Authorization: Bearer <token>` header.

| Method | Endpoint | Purpose |
|---|---|---|
| GET, POST | `/accounts` | List accounts with balances, or create one |
| GET, PATCH, DELETE | `/accounts/{account}` | Read, update or delete an account |
| GET, POST | `/accounts/{account}/transactions` | List transactions (25 per page), or add one |
| GET, PATCH, DELETE | `/transactions/{transaction}` | Read, update or delete a transaction |
| GET | `/accounts/{account}/summary?month=2026-10` | Totals by category; defaults to the current month |

Create a token and call the API locally:

```bash
php artisan tinker --execute 'echo App\Models\User::first()->createToken("cli")->plainTextToken;'

curl -H "Authorization: Bearer <token>" -H "Accept: application/json" \
  http://localhost:8000/api/accounts
```

## Run it locally

You need PHP 8.3+, Composer, Node 22+ and MySQL.

```bash
git clone https://github.com/RushilPrajapati1/PHP-Project.git ledger
cd ledger

mysql -u root -e "CREATE DATABASE ledger;"

composer setup        # installs dependencies, creates .env, migrates, builds the front end
php artisan db:seed   # optional: demo data
composer run dev      # starts Laravel and Vite at http://localhost:8000
```

`.env.example` assumes MySQL on `127.0.0.1` with user `root` and no password. Change the `DB_*` values in `.env` if yours differs.

The seeder creates `test@example.com` with the password `password`, and gives every user three accounts with 25 transactions each. It is meant for local use only.

## Tests

```bash
composer test         # Pint, PHPStan and the Pest suite
composer ci:check     # the above plus front-end lint and type checks (what CI runs)
```

The Pest suite covers authentication, ownership (one user cannot reach another's data), validation, and the monthly summary totals, for both the API and the web pages. Tests run against an in-memory SQLite database.
