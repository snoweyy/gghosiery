# GG Hosiery ERP + Website

Premium PHP 8 + MySQL MVP for the GG Hosiery public website and private ERP at `/erp`.

## Setup

1. Create a MySQL database and import `database/schema.sql`.
2. Set database credentials with environment variables if needed:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
3. Serve the project from the web root.
   - Apache: `.htaccess` routes all clean URLs to `index.php`.
   - PHP dev server: `php -S localhost:8000 index.php`
4. Open `/install` once to create the first Owner account. This creates `config/installed.lock`.
5. Log in at `/erp/login`.

## Security Notes

- ERP pages under `/erp` require authenticated PHP sessions.
- ERP APIs under `/api/erp/*` require authentication, CSRF, and role checks.
- Passwords use `password_hash()` and `password_verify()`.
- Database writes use PDO prepared statements.
- Public signup is not available.
- Demo login only works on localhost by default. For production set `APP_ENV=production` and do not enable `APP_DEMO_MODE`.
- Security headers are emitted by PHP and Apache `.htaccess`, including CSP, frame protection, MIME sniffing protection, referrer policy, permissions policy, and HSTS when HTTPS is active.
- `uploads/` blocks PHP/script execution. Keep this folder writable for images but never executable.
- Use HTTPS on production hosting and force HTTP to HTTPS at the hosting control panel or web server level.
- Do not expose `config/`, `database/`, `middleware/`, or `components/` publicly. The included `.htaccess` blocks direct Apache access.

## Google ERP Login

- ERP login now uses Firebase Authentication with the Google provider.
- In Firebase Console, enable Authentication -> Sign-in method -> Google.
- Add `localhost` to Firebase Authentication authorized domains for local testing.
- Firebase login only allows active ERP users whose Google email already exists in `users.email`; local demo mode can still create a demo session when MySQL is unavailable.
- ERP login is additionally restricted by `ERP_ALLOWED_LOGIN_EMAILS`, defaulting to `gandasarthak@gmail.com`. Add more emails as a comma-separated list when you want to authorize more people.
- Password login is hidden by default. On localhost only, use `/erp/login?password=1` as an emergency fallback.
- For production, set Firebase config values as hosting environment variables if they change from the defaults in `config/firebase.php`.

## Public User Login

- Public customers can log in at `/user/login` with Firebase Google login for cart and small order requests.
- Public website login stores `website_user` session data only and does not grant ERP access.
- ERP access remains separate and requires an internal `users` table account with owner/admin/employee permissions plus the ERP email allowlist.
- If MySQL is migrated, public Google users can be stored in `website_users` using `database/migrations/002_add_website_users.sql`.
