# Put MindStack Task Tracker online (InfinityFree)

InfinityFree has no terminal, so `php artisan` and `composer` cannot run on
the server. Everything is prepared on your computer and then uploaded.

Your local accounts, projects and tasks are not copied. The live site starts
empty and you create your account again there.

## 0. Pick the address

Laravel needs a whole `htdocs` folder to itself.

- If `mindstackdevtech.infy.click` already shows your company website, do
  **not** upload into its `htdocs`, or the company site is replaced. In the
  InfinityFree client area, add a second free subdomain to your account
  (for example `mindstacktracker.infy.click`). It gets its own folder:
  `mindstacktracker.infy.click/htdocs`.
- If that address is empty or you want the tracker there, use its `htdocs`.

Below, "your htdocs" means the folder for the address you picked.

## 1. Extract this kit into your project

Extract the zip into `C:\xampp1\htdocs\mindstack-task-tracker`. You now
have a `deploy` folder inside the project.

## 2. Build the upload folder

Open Command Prompt (the same kind of window where `php artisan serve`
works) and run:

```
cd /d C:\xampp1\htdocs\mindstack-task-tracker
deploy\prepare-deploy.bat
```

It creates `C:\xampp1\htdocs\mindstack-upload`: a clean copy of the app
with only the packages the live site needs, a new secret key, and the
`.htaccess` routing file. Your project folder is not changed.

At the end it says "None found. Good." if no file is too big for
InfinityFree. If it lists a file as TOO BIG, stop and send me the message.

## 3. Create the database

1. In the InfinityFree client area, open your hosting account, then
   **Control Panel**, then **MySQL Databases**.
2. Create a database, for example `mindstack`.
3. Write down the four details shown there: **MySQL Host name**,
   **Database name**, **Username** and **Password** (the password is your
   hosting account password, shown in the client area).
4. Click **phpMyAdmin** next to the new database.
5. Open the **Import** tab, choose `deploy\mindstack-database.sql`, and
   click **Import** (or **Go**). You should see 18 tables on the left.

## 4. Fill in the live settings

Open `C:\xampp1\htdocs\mindstack-upload\.env` in Notepad and fill in the
lines marked FILL IN:

- `APP_URL`: your site address, for example `https://mindstacktracker.infy.click`
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: from step 3
- `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`: copy them from your
  local `.env` (the Gmail address and the 16-character app password)

Leave `APP_KEY` as it is. The script already filled it in.

Keep a copy of this filled-in `.env` somewhere safe outside the project.
Do not put it on GitHub.

## 5. Upload with FileZilla

1. In the client area, open your hosting account and find **FTP details**:
   hostname, username and password.
2. In FileZilla, enter them in the Quickconnect bar (port 21) and connect.
3. On the right (the server), open your htdocs folder from step 0. Delete
   InfinityFree's placeholder files there if it has any (such as
   `index2.html`). Do not delete anything if this is the company website's
   folder; go back to step 0 instead.
4. On the left (your computer), open `C:\xampp1\htdocs\mindstack-upload`.
5. Select **everything inside it**, including `.env` and `.htaccess`, and
   drag it into htdocs on the right.

The upload is several thousand files and can take 30 minutes or more. When
it finishes, check FileZilla's **Failed transfers** tab at the bottom. If it
lists files, right-click them and choose **Reset and requeue**, then process
the queue again.

The server's htdocs must look like this (not one folder deeper):

```
htdocs/
├── .env
├── .htaccess
├── app/
├── bootstrap/
├── config/
├── public/
├── resources/
├── routes/
├── storage/
└── vendor/
```

## 6. Open the site

1. Open your address. The log in page should appear.
2. Click **Create one** and make your account. You should get the
   thank-you message and email.
3. Check that private files are hidden: open `your-address/.env`. It must
   show the "Page not found" page, never your settings.

## 7. Turn on HTTPS

1. Open your site with `https://` at the start.
2. If the browser shows a security warning instead, go to **SSL
   certificates** in the InfinityFree client area and request a free
   certificate for the address. It can take a while to be issued.
3. When `https://` works, change these in the server's `.env` (right-click
   it in FileZilla, choose **View/Edit**, then save and upload):

```
APP_URL=https://your-address
SESSION_SECURE_COOKIE=true
```

## If something goes wrong

- **"HTTP ERROR 500" or a blank page:** in FileZilla, download
  `htdocs/storage/logs/laravel.log` and send me the last error in it.
  Do not turn `APP_DEBUG` on for the live site.
- **"Database connection" errors:** `DB_HOST` must be the host from step 3,
  not `localhost`.
- **Log in does not stick (you go back to the log in page):** set
  `SESSION_SECURE_COOKIE=false` until `https://` works.
- **Emails do not arrive:** check the Gmail app password, and look in Spam.
- **Missing pages or styles:** a file failed to upload. Upload that folder
  again.

## Updating the live site later

For a small change, upload only the changed files over the old ones.

For a full update, delete `C:\xampp1\htdocs\mindstack-upload`, run step 2
again, copy your saved live `.env` over the new one, and upload.

A database change (a new table) needs a new SQL file. I can make one.

## Limits of free hosting

- No scheduled tasks or background workers (the app does not need them).
- Emails are sent through Gmail, which caps how many you can send per day.
- Free accounts have a daily limit on requests. An open team chat or
  personal message page checks for new messages every 4 seconds, which
  uses that limit faster.
- The site can be slow at times. For a real launch with many users, paid
  hosting is the better choice.
