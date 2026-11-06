# TolCare — Care Home Shift Scheduling

A PHP + MySQL web app that automates shift scheduling for a care home.
The manager used to build every schedule by hand in Excel; TolCare
generates the full agenda from employee availability, staffing
requirements and 2-week rotation patterns.

## Features

- **Employee catalog** with availability (unavailable dates, date ranges,
  custom hours per day) and a calendar view
- **Staffing parameters:** required staff per team, shift and position
- **2-week rotation patterns** per team
- **Agenda builder:** generates the shift matrix, **saves** it to the
  database (`agenda_shifts`), publishes it (Draft → Published, locked
  after publishing) and **exports it to CSV** (opens in Excel)
- **Login** with hashed passwords (`password_hash` / `password_verify`);
  every page requires a session
- **All database queries use prepared statements** — no SQL injection

## Tech

PHP 8 · MySQL / MariaDB · HTML / CSS / JavaScript · XAMPP

## Setup (XAMPP, Windows)

1. Copy this folder to `C:\xampp\htdocs\` (keep the name you want, e.g.
   `tolcare`).
2. Edit `config/db.php` with your own database credentials.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open phpMyAdmin: http://localhost/phpmyadmin
5. Create a database called **tolcare** (or use an existing one).
6. Import **`tolcare.sql`** (current schema + test data only).
7. Import **`sql/001_users_table.sql`** (login users table).
8. Open http://localhost/tolcare/login.php — no users exist yet, so the page
   asks you to **create your admin account** (username 3+ chars, password
   8+ chars). After that it works as a normal login.
9. Log in and use the app. Log out with the **Exit** link in the menu.

## Project history

- Built in early 2026 for a real care-home manager who ran schedules in
  Excel. Quoted as a 6–8 week project (CAD $2,500); the client never
  replied, so the project became a portfolio piece.
- Nov 2026: added login, fixed SQL injection everywhere, wired the Save
  button (with Finish/Publish and CSV export), and cleaned the project
  from 24 MB down to ~250 KB.

## Known limitations

- `agenda/edit.php` is empty (Edit Agenda was never built).
- The agenda builder only shows one team/shift tab at a time (Team 1 / Day
  by default) — it does not list all teams and shifts.
- `schedule/schedule_settings.php` does not load the saved start date into
  the form (it saves correctly, it just shows empty when you reopen it).
- Access logging writes one row to `logs` on every page load.

## Files

- `index.php` — home / dashboard
- `login.php`, `logout.php`, `includes/auth.php` — login and session guard
- `config/db.php` — database connection (edit with your own credentials)
- `employees/` — catalog, add/edit/view, availability + calendar
- `agenda/` — create agenda, agenda builder, save / finish / CSV export
  (`shift_logic.php` holds the generation logic shared by the preview
  and the save, so saved = shown)
- `parameters/` — staffing parameters (required staff per team/shift/position)
- `schedule/` — rotation pattern + schedule settings + preview
- `tolcare.sql` — current database (schema + test data only, no real data)
- `sql/001_users_table.sql` — users table for login
- `sql/procedures.sql` — old stored procedures (reference only — they were
  written for an older schema)
- `uploads/` — attachments (empty by default)
