# Railway backend deployment

Deploy this folder as the Railway backend service.

## Railway service

1. Create a new Railway project.
2. Add a MySQL database service.
3. Add a GitHub service from this repository.
4. Set the service root directory to `backend`.
5. Railway will build with `backend/Dockerfile`.
6. Generate a Railway domain for the backend service.

The container listens on Railway's `$PORT`, as required for public HTTP services.

## Backend variables

Set these variables on the Railway backend service:

```env
GEMINI_API_KEY=your_gemini_key
```

For MySQL, either connect the Railway MySQL service variables directly or set these manually:

```env
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
DB_CHARSET=utf8mb4
```

The app also understands Railway's native `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD` variables.

## Verify

After deployment, open:

```text
https://YOUR-RAILWAY-BACKEND-DOMAIN/api/health
```

Expected response:

```json
{"success":true,"status":"ok"}
```

## Vercel frontend

After Railway gives you the backend URL, update Vercel environment variables:

```env
VITE_API_BASE=https://YOUR-RAILWAY-BACKEND-DOMAIN
VITE_AI_API_BASE=https://YOUR-RAILWAY-BACKEND-DOMAIN
```

Then redeploy the Vercel frontend.
