# TolCare — Code Review (2026-11-06)

Source: `project_0_m3zd.zip` (uploaded by Juan), extracted to this folder.
Stack: PHP + MySQL (MariaDB) on XAMPP. ~2,600 lines. DB name in `config/db.php`: `tolcare`.

## Nov 6, 2026 — login + SQL injection fix + cleanup (DONE, tested live)

User chose fix-first (not GitHub yet), with one explicit limit: **do NOT
make Save work** — only login, injection fixes, and cleanup.

- **Login added.** `login.php` (with first-run setup mode: when the users
  table is empty it creates the first admin account; no default password
  shipped), `logout.php`, `includes/auth.php` (session guard, first include
  on every page incl. AJAX endpoints), `sql/001_users_table.sql` migration.
  Passwords hashed with `password_hash`/`password_verify`, session id
  regenerated on login. Menu "Exit" now points to `logout.php`. Header
  shows the logged-in username instead of the Windows `USERNAME`.
- **SQL injection fixed.** All queries using user input converted to
  prepared statements (`prepare`/`bind_param`/`get_result`) in: add,
  edit, list (search), view, availability (incl. overlap checks and the
  `NULLIF(?,'')` insert), load_events, agenda create (both INSERTs),
  agenda_builder (all 7 queries + `shift` allowlist Day/Night),
  parameters (all 7 queries), load_matching, schedule_settings (both
  INSERTs). Remaining `$conn->query()` calls are static, no-input queries
  only. Upload filenames now go through `basename()`.
- **Cleanup.** Deleted `dashboard/` (XAMPP junk), unused images (only
  `logo.jfif` kept), both stale `mcdougall.sql` dumps (the two stored
  procedures preserved in `sql/procedures.sql` — note: written for the old
  Spanish tables, reference only), and the personal file from `uploads/`
  (folder kept empty with `.gitkeep` so attachments still work). Line
  endings normalized to LF. Project: 24 MB → ~250 KB.
- **Verified live** (PHP 8.3 + MariaDB test install, then removed): all 22
  PHP files pass `php -l`; every prepared statement balanced (placeholders
  = types = vars); functional test — unauthenticated access redirects to
  login, setup creates admin, login works, employee list shows 18, adding
  an employee works, login injection (`admin' OR '1'='1`) fails, search
  injection returns 0 rows, logout works.
- **Still NOT done (user's decision):** builder Save stays unwired.
  Known limitations left as-is: `agenda/edit.php` empty; builder shows one
  team/shift tab only; schedule_settings form doesn't reload the saved
  start date; logs table grows one row per page load.

## Nov 6, 2026 — titles fix (login form + browser tabs)

User reported the login page's form "doesn't have a title" after installing
the fixed zip. Fix: login form now has a proper heading — "Log In" in
normal mode, "Create Admin Account" in first-run setup mode (new
`.login-title` style in `css/style.css`, white/centered to fit the blue
card). Also every inner page now has a real browser-tab title
(`includes/header.php` maps the script name to a title, e.g. "Edit
Employee — TolCare", "Employee Catalog — TolCare") instead of just
"TolCare" on all tabs. Verified live: login page shows the "Log In"
heading; edit/list pages show their titles. Updated zip delivered
(`TolCare-fixed-2026-11-06.zip`, rebuilt).

## Nov 6, 2026 — step 1 DONE: Save button + Finish + CSV export + logged-in user

- **Save wired.** New `agenda/shift_logic.php` holds the generation logic
  shared by the preview and the save, so saved = shown. New
  `agenda/save.php` persists the generated matrix into `agenda_shifts`
  (prepared statements, login required). Idempotent: re-saving replaces
  the tab's future shifts, never duplicates. Past dates are locked:
  skipped on save (and old saved past shifts are kept, not deleted).
- **Finish wired.** New `agenda/finish.php`: Draft → Published, with a
  confirm dialog. A published agenda is locked (Save refused, buttons
  disabled, lock note shown) but can still be exported.
- **CSV export.** New `agenda/export.php`: downloads the saved agenda for
  the current tab (Date, Shift, Team, Employee, Position, Start, End).
  Redirects back with a message if nothing is saved yet.
- **Logged-in user always visible.** `includes/header.php` now shows
  "Logged in as: **name** | Date | Log out" on every page.
- **Also fixed:** removed the closing `?>` from `config/db.php` — its
  trailing newline was leaking a blank line at the top of the CSV (and
  was a latent headers-already-sent risk). Added `.btn-finish`,
  `.btn-export` and disabled-button styles to `css/agenda_builder.css`.
- **Verified live** (PHP 8.3 + MariaDB test install): matrix render
  byte-identical to before the change (67+73 and 70+70 cells on the two
  test agendas); save 70 shifts, re-save still 70; all-past agenda saves
  0 (67 skipped); export 71 lines, no leading blank line; publish +
  lock work; anonymous save redirects to login; header shows the user.
- **Delivered as `TolCare-paso1-save.zip`** — only the 8 changed/new
  files, with their folders (agenda/, includes/, config/, css/).

His screenshot showed the real problem: the three input fields on the
login/setup form had NO visible labels (Username / Password / Confirm
password were missing). Cause: `.section-box` has a white background but
no text color, and inside the blue login card the text color is white —
white text on a white box. Fix: `.section-box` now sets `color:#333`, and
the bare text labels in `login.php` became real `<label>` elements (bold,
block). Same fix covers both login and setup forms. Verified live on the
test server: labels served in the HTML and the CSS rule served with
`color:#333`. Zip rebuilt again.

## Original review

## What is built (working)

- **Employee Catalog** (`employees/`): add / edit / list / view, with availability
  management in 3 types: SingleDate, DateRange, SpecificHours.
- **Agenda creation** (`agenda/create.php`): creates agenda header rows in
  `agendas` (start_date, end_date, status = Draft).
- **Agenda builder** (`agenda/agenda_builder.php`): AUTO-GENERATES the matrix.
  Per cell it applies, in priority order: SpecificHours → blocked
  (SingleDate / DateRange) → rotation pattern (2-week rotation,
  `team_rotation_pattern`) → Day 06:00–18:00 / Night 18:00–06:00.
  Staffing comes from `staffing_parameters` (required qty per
  team/shift/position). Tabs per team + shift. This is the auto-scheduler —
  it goes beyond the written proposal.
- **Parameters** (`parameters/parameters.php`): required staff per
  team/shift/position (DELETE + INSERT).
- **Schedule settings** (`schedule/schedule_settings.php`): rotation patterns.
- **Access logging**: `includes/header.php` inserts into `logs` on every page.
- **Stored procedure** `exportar_calendario` (root `mcdougall.sql`, Jan 2026):
  dynamic-SQL pivot that exports the calendar matrix (employee × dates).
- Schema is normalized with foreign keys (older Spanish-named dump in
  `data/mcdougall.sql`, Nov 2025).

## Gaps / issues (honest)

1. **No login / authentication at all.** No `session_start`, no password, no
   login page. Anyone with the URL can see and change everything. (Proposal
   said "access logging only" — the logging exists — but zero auth is a real
   security gap for employee data.)
2. **SQL injection.** No prepared statements anywhere. `$_GET`/`$_POST`
   values go directly into queries (e.g. `employees/add.php` inserts raw
   `$_POST`; `agenda_builder.php` takes `shift` raw from `$_GET`).
   Fix: `mysqli_prepare` / `bind_param` everywhere.
3. **`agenda/edit.php` is empty (0 lines).** Edit Agenda was never built.
4. **Save button in the builder does nothing.** The generated matrix is
   display-only; shifts are computed on the fly and never saved to the DB,
   so there is no finalize step. No Excel/CSV export code exists either
   (promised in the proposal).
5. **Schema drift — RESOLVED 2026-11-06.** Juan exported the live database
   (`tolcare.sql`, generated Nov 6, 2026; saved in this folder). It has 9
   tables, and all 8 the code uses are there: `agendas`, `availability`,
   `employees`, `logs`, `positions`, `schedule_settings`,
   `staffing_parameters`, `team_rotation_pattern` — plus `agenda_shifts`
   (the table for persisted shifts: agenda_id, shift_date, shift_type,
   emp_id, team, position_id, start/end time). Data: 18 test employees
   (generic names, all created 2026-02-24), 1 agenda, 3 availability
   records, 3 positions, 10 staffing parameters, 4 rotation patterns
   (2 teams × 2 weeks). `agenda_shifts` is EMPTY and `schedule_settings`
   is empty — confirming the builder's Save is unwired. `logs` has 665
   rows of access-log noise — trim before publishing. The old Spanish
   `mcdougall` dumps remain in the zip only as history (root copy also has
   `asistencias`/`nominas` + the `exportar_calendario` procedure).
6. **No Java files in the zip.** The project is PHP-only as shared.
7. **Zip hygiene for GitHub:** remove `dashboard/` (XAMPP junk), the large
   AI images in `img/`, and `uploads/` (contains a personal file — not
   opened). Also remove `data/` duplicate dump; keep one current dump.

## Suggested next steps (portfolio path)

1. Clean the folder (remove dashboard/, img junk, uploads/).
2. Fix #2 (prepared statements) and #1 (simple login) — the two fixes that
   matter most for showing the code to employers.
3. Wire the Save button (persist generated shifts) OR document the builder
   as a live preview; implement or drop Edit Agenda.
4. Export the CURRENT database schema from phpMyAdmin into the repo
   (`schema.sql`) + a small README (what it is, how to run on XAMPP).
5. Publish on GitHub as a portfolio project.
