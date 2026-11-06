# TolCare — Plan (Nov 6, 2026)

Goal: turn TolCare into a complete portfolio piece — working end to end,
live demo, video, and public posts. We do each step when Juan says so,
in this order.

## 1. Develop the Save button — DONE (Nov 6, 2026)
- [x] Wire the builder's Save button to insert the generated matrix into
      `agenda_shifts` (table already exists and is empty).
- [x] Add "finalize" (Draft → Published) and prevent editing past dates.
- [x] Add Excel/CSV export of the agenda (was promised in the proposal).
- [x] Test the full flow live: create agenda → generate → save → reopen.

Details: shared generation logic in `agenda/shift_logic.php` (preview and
Save use the same code, so saved = shown). Save is idempotent (re-saving
replaces, never duplicates) and skips past dates (locked). Finish sets
status to Published and locks the agenda (Save refused after that, export
still works). Export is CSV (`agenda/export.php`). The logged-in user now
shows as "Logged in as: <name>" with a Log out link on every page
(`includes/header.php`). Delivered as a small zip with only the changed
files: `TolCare-paso1-save.zip` (8 files). Verified live: matrix render
byte-identical to before the change; save 70 shifts / re-save still 70 /
past agenda saves 0 (67 skipped); export 71 lines; publish + lock work;
anonymous save redirects to login.

## 2. Put it live (public demo) — PENDING
- [ ] Choose a host with PHP + MySQL (free tier first).
- [ ] Upload the cleaned project files.
- [ ] Create the database on the host and import `tolcare.sql` +
      `sql/001_users_table.sql`.
- [ ] Update `config/db.php` with the host's credentials.
- [ ] Create a demo login (test user) and verify the live demo works.
- [ ] Note the public URL for the resume and LinkedIn.

## 3. Record the demo video — PENDING
- [ ] 1-minute screen recording: login → add employee → generate agenda
      → save → (show the live demo URL on screen).
- [ ] Short script/outline before recording (what to say and click).
- [ ] Keep it to the working features only (no unfinished parts).

## 4. Publish on LinkedIn + resume — PENDING
- [ ] LinkedIn post 1: the problem (manager did schedules in Excel).
- [ ] LinkedIn post 2: the solution (auto-generated agenda) + video.
- [ ] LinkedIn post 3: what I learned (found SQL injection in my own
      code and fixed it; added login).
- [ ] Add TolCare to the IT resume (Projects section, with repo + demo links).
- [ ] Do NOT publish real employee names or the care home's name — test
      data only.

## Done already
- [x] Login + SQL injection fixes + cleanup (Nov 6, 2026).
- [x] Login form titles + per-page tab titles (Nov 6, 2026).
- [x] Invisible field labels fixed (Nov 6, 2026).
