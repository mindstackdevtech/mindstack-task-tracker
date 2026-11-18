# Update 2: team chat, account page, calendar, safer login, error pages

Install update 1 (forgot password, emails, notifications) first.

## Install

1. Right-click the zip, choose **Extract All**, and set the destination to
   `C:\xampp1\htdocs\mindstack-task-tracker`. Choose **Replace the files in
   the destination** when asked.
2. In the project folder run:

   ```
   php artisan migrate
   php artisan view:clear
   php artisan serve
   ```

`php artisan migrate` only adds the `messages` table. Your data stays.
Do not use `migrate:fresh`.

## What is new

- **Team chat:** each project has a Chat button. Members can message each
  other; new messages appear within a few seconds without reloading. Other
  members get one bell notification per chat until they read it.
- **Account page:** "Account" next to "Log out". Change your name, email
  and password.
- **Calendar:** a month view of due dates across your projects, with an
  "All tasks / My tasks" switch. On phones it lists the days that have tasks.
- **Safer login:** log in and forgot-password allow 6 tries a minute.
- **Error pages:** branded pages for "not found", "no access", "page
  expired" and "too many attempts".

## Files

New:

- `app/Models/Message.php`
- `app/Http/Controllers/ChatController.php`
- `app/Http/Controllers/AccountController.php`
- `app/Http/Controllers/CalendarController.php`
- `database/migrations/2026_11_16_000002_create_messages_table.php`
- `resources/views/projects/chat.blade.php`
- `resources/views/account/edit.blade.php`
- `resources/views/calendar/index.blade.php`
- `resources/views/errors/layout.blade.php`, `403`, `404`, `419`, `429`
- `public/js/chat.js`

Replaced:

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/projects/show.blade.php`
- `public/css/extras.css`
