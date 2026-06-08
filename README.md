# Beyond 66° — Local Setup Guide

## Required Software

| Software | Where to get it |
|----------|-----------------|
| XAMPP (any recent version) | xampp.org |
| Node.js 20 or newer | nodejs.org |
| Python 3.10 or newer | python.org |

---

## Step 1 — Clone the Repository

Open PowerShell and run:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/Elleyxx/Beyond66.git vue-assignment
cd vue-assignment
```

> All credentials and environment files are already included — no extra configuration needed.

---

## Step 2 — Start MySQL

1. Open **XAMPP Control Panel**
2. Click **Start** next to **MySQL**

> Apache does not need to be started.

---

## Step 3 — Create the Database

```powershell
C:\xampp\php\php.exe backend\scripts\setup_beyond66_db.php
```

Wait for:

```
Beyond66 database setup complete.
```

---

## Step 4 — Install Dependencies

```powershell
npm install
cd frontend
npm install
cd ..
cd backend
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
cd ..
```

---

## Step 5 — Start the Website

```powershell
npm run dev
```

Then open **http://127.0.0.1:5173** in your browser.

---

## Demo Account

| Username | Password |
|----------|----------|
| `demo` | `password123` |

---

## Health Check URLs

| Service | URL | Expected response |
|---------|-----|-------------------|
| PHP backend | http://127.0.0.1:8000/api/health | `{"success":true,"status":"ok"}` |
| AI service | http://127.0.0.1:5000/api/health | `{"success":true,"status":"ai-ok"}` |

---

## Troubleshooting

**MySQL connection failed** — Make sure MySQL is started in XAMPP Control Panel.

**PHP not found** — Install XAMPP at `C:\xampp`.

**Port already in use** — Close any other running servers on ports 5173, 8000, or 5000 and try again.

**Manual start (if `npm run dev` fails)** — Open three separate terminals:

```powershell
# Terminal 1 — PHP backend
cd C:\xampp\htdocs\vue-assignment\backend
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

```powershell
# Terminal 2 — Python AI service
cd C:\xampp\htdocs\vue-assignment\backend
venv\Scripts\activate
python ai_app.py
```

```powershell
# Terminal 3 — Vue frontend
cd C:\xampp\htdocs\vue-assignment\frontend
npm run dev
```
