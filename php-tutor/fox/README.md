# Fox Project

A simple PHP project using Slim Framework 4 and Swagger PHP.

## Installation

```bash
composer install
```

## Running the Project

```bash
pwsh serve.ps1            # serves on http://localhost:2977
# or:
php -S localhost:2977 -t public/
```

## Response envelope

Every `/api/*` endpoint returns the same envelope, built by
`src/Support/BaseResponse.php`:

```json
{ "code": 200, "message": "ok", "data": { ... } }
```

- `code` mirrors the HTTP status, so a client switches on one field.
- Success: `data` is the payload, status 200/201.
- Error: `data` is `null`, status 400/404 with a readable `message`.
- DELETE success is `204` with an empty body (no envelope possible).

List endpoints nest page fields under `data`:

```json
{ "code": 200, "message": "ok",
  "data": { "list": [ ... ], "total": 120, "limit": 50, "offset": 0, "hasMore": true } }
```

`limit` / `offset` / `hasMore` are only present when the endpoint is paginated.

**Not enveloped:** `/swagger` (HTML), `/swagger/json` (must stay a raw OpenAPI
document), `/api/logs/{date}?raw=1` (a file dump), and `/` + `/hello/{name}`
(plain-text home endpoints). They are not JSON API payloads.

## API Endpoints

- `GET /` - Home page
- `GET /hello/{name}` - Say hello to someone
- `GET /swagger` - Swagger UI (rendered HTML)
- `GET /swagger/json` - OpenAPI spec (JSON)
- `GET /api/logs` - List available log days (size, entry count, level breakdown)
- `GET /api/logs/{date}` - Entries of one day, e.g. `/api/logs/2026-09-08`
  - `?level=ERROR` filter by level
  - `?keyword=swagger` substring search on the raw line
  - `?limit=500&offset=0` pagination (limit max 5000)
  - `?raw=1` return the untouched file content as `text/plain`

## Project Structure

```
fox/
├── public/
│   ├── index.php          # Entry point
│   └── .htaccess          # Apache rewrite rules
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php      # Home controller with Swagger attributes
│   │   ├── UserController.php      # /api/users CRUD
│   │   ├── LogController.php       # /api/logs day listing + day reader
│   │   └── SwaggerController.php   # Serves Swagger UI + OpenAPI JSON
│   ├── Services/
│   │   ├── Logger.php              # Monolog wrapper, rotates daily as app-YYYY-MM-DD.log
│   │   ├── LogReader.php           # Discovers, parses, filters and pages those files
│   │   └── Store.php               # In-memory user store
│   ├── Routes/
│   │   └── Web.php                 # Route definitions
│   ├── Support/
│   │   └── BaseResponse.php        # Unified JSON envelope for /api/* responses
│   └── Swagger/
│       └── ApiInfo.php             # OpenAPI Info annotation
├── cache/                   # Generated swagger.json (1h TTL)
├── logs/                    # Rotating log files, one per day
├── serve.ps1                # Starts the PHP built-in server on :2977
├── composer.json
└── README.md
```

## Testing

### PHP Built-in Server

```bash
# Start server
php -S localhost:2977 -t public/

# Test endpoints
curl http://localhost:2977/
curl http://localhost:2977/hello/world
curl http://localhost:2977/swagger       # Swagger UI
curl http://localhost:2977/swagger/json  # OpenAPI spec

# Logs
curl http://localhost:2977/api/logs                          # which days exist
curl http://localhost:2977/api/logs/2026-09-08               # parsed entries
curl http://localhost:2977/api/logs/2026-09-08?level=ERROR   # by level
curl http://localhost:2977/api/logs/2026-09-08?keyword=swagger
curl "http://localhost:2977/api/logs/2026-09-08?limit=20&offset=20"
curl http://localhost:2977/api/logs/2026-09-08?raw=1         # untouched file
```

> Swagger UI assets are loaded from `unpkg.com`, so the `/swagger` page needs
> internet access. The bundle version is pinned in `SwaggerController` —
> bump `SWAGGER_UI_VERSION` there to upgrade.

> **The `/api/logs` endpoints are unauthenticated and dump the log files as
> plain JSON over HTTP.** The date path segment is validated against
> `Y-m-d` and the resolved path is re-checked inside `logs/`, so there is no
> path traversal, but anyone who can reach the app can read every log line —
> including request URIs and client IPs. Keep it behind a dev-only host,
> firewall, or auth layer before exposing it.

> Log filenames and the `datetime` field use the PHP timezone, which is UTC
> here. Check `date_default_timezone_get()` if the day boundary surprises you.
