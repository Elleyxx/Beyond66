# Beyond 66° — Local Setup Guide

This guide is written for a lecturer or examiner running the project locally after cloning from GitHub.

The project contains:

- `frontend/` — Vue 3 + Vite website
- `backend/` — PHP REST API + Python Flask AI service
- MySQL database — stores users, planners, community posts, and saved items

---

## Required Software

Please install these before starting:

| Software | Version | Where to get it |
|----------|---------|-----------------|
| XAMPP | Any recent | xampp.org — provides MySQL and PHP together |
| Node.js | 20 or newer | nodejs.org |
| Python | 3.10 or newer | python.org |

> XAMPP is the easiest way to get both MySQL and PHP on Windows. If PHP is not in your system PATH, the project will still work as long as XAMPP is installed at `C:\xampp`.

---

## Step 1 — Clone the Repository

Open PowerShell and run:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/Elleyxx/Beyond66.git vue-assignment
cd vue-assignment
```

> The `.env` file with database credentials is already included in the repository — no manual configuration needed.

---

## Step 2 — Start MySQL

1. Open **XAMPP Control Panel**
2. Click **Start** next to **MySQL**

> Apache does not need to be started. Only MySQL is required.

---

## Step 3 — Create the Database

Run the included setup script to create the `beyond66` database and all required tables:

```powershell
C:\xampp\php\php.exe backend\scripts\setup_beyond66_db.php
```

You should see output ending with:

```
Beyond66 database setup complete.
```

> This only needs to be done once. Running it again is safe — it will not overwrite existing data.

---

## Step 4 — Install Dependencies

From the project root (`C:\xampp\htdocs\vue-assignment`), run:

```powershell
npm install
cd frontend
npm install
cd ..
```

Then set up the Python environment for the AI service:

```powershell
cd backend
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
cd ..
```

---

## Step 5 — Start the Website

From the project root, run:

```powershell
npm run dev
```

This starts all three services automatically:

| Service | URL |
|---------|-----|
| Website | http://127.0.0.1:5173 |
| PHP backend | http://127.0.0.1:8000 |
| Python AI service | http://127.0.0.1:5000 |

Keep this terminal window open while using the website.

---

## Step 6 — Open the Website

Once the terminal shows all services are running, open:

```
http://127.0.0.1:5173
```

The Beyond 66° homepage should appear.

---

## Verify Everything is Working

Open these URLs in the browser to confirm the backend services are running:

- **PHP backend:** http://127.0.0.1:8000/api/health → `{"success":true,"status":"ok"}`
- **AI service:** http://127.0.0.1:5000/api/health → `{"success":true,"status":"ai-ok"}`

---

## Demo Account

A demo account is created automatically when the database is first set up:

| Field | Value |
|-------|-------|
| Username | `demo` |
| Password | `password123` |

You can also register a new account from the Register page.

---

## Stopping the Website

Go back to the terminal running `npm run dev` and press `Ctrl + C`.

---

## Manual Start (if `npm run dev` does not work)

If the automatic start fails, open three separate terminal windows:

**Terminal 1 — PHP backend:**
```powershell
cd C:\xampp\htdocs\vue-assignment\backend
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

**Terminal 2 — Python AI service:**
```powershell
cd C:\xampp\htdocs\vue-assignment\backend
venv\Scripts\activate
python ai_app.py
```

**Terminal 3 — Vue frontend:**
```powershell
cd C:\xampp\htdocs\vue-assignment\frontend
npm run dev
```

Then open http://127.0.0.1:5173 in the browser.

---

## Troubleshooting

**MySQL connection failed**
- Make sure MySQL is started in XAMPP Control Panel
- Check that `backend\.env` has `DB_USER=root` and `DB_PASS=` (empty)

**PHP not found**
- Install XAMPP at `C:\xampp` — the project will find PHP automatically

**Python packages missing**
```powershell
cd backend
venv\Scripts\activate
pip install -r requirements.txt
```

**Frontend packages missing**
```powershell
cd frontend
npm install
```

**Port already in use**
- Another application may be using port 5173, 8000, or 5000
- Close any other running servers and try again
