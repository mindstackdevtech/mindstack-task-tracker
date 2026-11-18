# Security in MindStack Task Tracker

## What the app does for you

**Accounts**
- Passwords are stored hashed (bcrypt), never as plain text.
- Passwords need 8+ characters with a capital letter, a small letter and a number.
- Log in, sign up, forgot password and reset password allow 6 tries a minute.
- Failed logins are written to `storage/logs/laravel.log` with the email and IP address.
- The log in error is the same for a wrong email and a wrong password, and
  "forgot password" answers the same whether or not the email is registered,
  so neither can be used to find out who has an account.
- A new session is started at log in and destroyed at log out.
- Password reset links expire after 60 minutes and work once.
- Changing your email needs your password, and the old address is warned.
- Changing or resetting your password sends you an alert and signs out
  "Keep me logged in" on other devices.

**Access**
- Every page except log in, sign up and password reset needs a logged-in user.
- Every project page, task, comment, checklist item, chat message, export and
  activity list checks that you are a member of that project.
- Only the owner can delete a project, remove members or reset the invite link.
- Only the author can delete a comment.
- A task can only be assigned to a member of its project.

**Requests**
- Every form carries a CSRF token, so other websites cannot submit forms as you.
- All database access goes through Laravel's query builder with bound
  values, which prevents SQL injection.
- Everything users type is escaped when shown, which prevents script
  injection (XSS). Chat messages are added to the page as plain text.
- Invites, chat messages, comments and exports are rate limited.
- Exported spreadsheets neutralise cells that start with `=`, `+`, `-` or `@`
  so they cannot run as formulas.

**Browser headers** (added by `app/Http/Middleware/SecurityHeaders.php`)
- The app cannot be embedded in a frame on another site.
- Scripts, styles, fonts and images load only from the app and Google Fonts.
- Forms can only submit back to the app.
- Logged-in pages are not stored by the browser.
- On HTTPS, the browser is told to always use HTTPS.

## What you must do when you put it online

Set these in `.env` on the live server:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
```

- `APP_DEBUG=false` matters most. With it on, an error page shows your
  code, database queries and cookies to whoever caused the error.
- Use HTTPS. Without it, passwords travel unencrypted.
- Never commit `.env`. If it was ever pushed to GitHub, change the app key
  (`php artisan key:generate`), your database password and your mail app
  password.
- Point the web server at the `public` folder only, so `.env`, `storage`
  and `database` cannot be opened from the browser.
- Use MySQL (or keep the SQLite file outside the public folder) and back up
  the database regularly.
- Keep packages updated: run `composer update` from time to time, and
  `composer audit` to list known vulnerabilities.

## What is not included

- Email verification (anyone can sign up with an address they do not own)
- Two-factor authentication
- An admin screen to block users
- Automatic sign-out of sessions already open on other devices after a
  password change (only "Keep me logged in" is cancelled)

This checklist is a hardening pass, not a professional security audit.
