# Update: forgot password, emails and notifications

## 1. Copy the files

Copy everything in this folder into your project
(`C:\xampp1\htdocs\mindstack-task-tracker`), keeping the folder structure,
and let it replace the existing files.

New files:

- `app/Notifications/AppNotification.php`
- `app/Support/Notifier.php`
- `app/Http/Controllers/PasswordResetController.php`
- `app/Http/Controllers/NotificationController.php`
- `database/migrations/2026_11_16_000001_create_notifications_table.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/notifications/index.blade.php`
- `public/css/extras.css`

Replaced files:

- `routes/web.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/TaskController.php`
- `app/Http/Controllers/CommentController.php`
- `app/Http/Controllers/ProjectMemberController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/projects/show.blade.php`

## 2. Add the new table

```bash
php artisan migrate
```

This only adds the `notifications` table. Your accounts and projects stay.
(Do not use `migrate:fresh` this time.)

## 3. Set up email

Out of the box Laravel does not send real email. With `MAIL_MAILER=log`
(the default) every email is written to `storage/logs/laravel.log`, which
is enough to test: open that file to see the reset link or the invitation.

To send real email, put your mail provider's SMTP details in `.env`.
Example for a Gmail account:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-address@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_FROM_ADDRESS=your-address@gmail.com
MAIL_FROM_NAME="MindStack Task Tracker"
```

For Gmail, `MAIL_PASSWORD` is an "app password" created in your Google
account's security settings (2-Step Verification must be on). It is not
your normal Gmail password.

Also set `APP_URL` to the address people use to reach the app, because the
links in emails are built from it:

```
APP_URL=http://127.0.0.1:8000
```

After changing `.env`, run `php artisan config:clear` and restart
`php artisan serve`.

`.env` is not uploaded to GitHub, so these details stay private.

## What changed

- **Forgot password:** "Forgot your password?" on the log in page emails a
  reset link that works for 60 minutes.
- **Invitation email:** in a project's Invite box, type an email and choose
  "Send invitation". People with an account are added and notified; others
  get an email with a link to join.
- **Thank you messages:** after signing up, a thank-you message on screen,
  a notification and an email. After logging in, a "Welcome back" message.
- **Notifications:** a bell in the menu with an unread count. You get a
  notification (and an email) when someone assigns you a task, comments on
  a task you created or are doing, completes a task you created, adds you
  to a project, or joins your project.
