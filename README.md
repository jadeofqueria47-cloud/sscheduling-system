
# scheduling system- Database setup


<form method="post" action="/journal/register.php">This folder contains example PHP files to connect to MySQL (XAMPP) and basic auth endpoints.

Files added:

- `db_connect.php` - creates a PDO instance and connects to MySQL. Edit credentials inside when needed.
- `init_db.sql` - SQL to create `journal_db` and a `users` table.
- `register.php` - example registration endpoint (POST name,email,password).
- `login.php` - example login endpoint (POST email,password).

Quick setup:

1. Start XAMPP and ensure Apache + MySQL are running.
2. Open phpMyAdmin (http://localhost/phpmyadmin) and import `init_db.sql` or run its contents in SQL tab.
3. Edit `db_connect.php` if your MySQL user/password differ (default XAMPP: user `root` with empty password).
4. Use the following form examples in your `auth.html` to POST to the endpoints (example below):

Example register form (insert into `auth.html`):

  <input name="name" required placeholder="Name">
  <input name="email" type="email" required placeholder="Email">
  <input name="password" type="password" required placeholder="Password">
  <button type="submit">Register</button>
</form>

Example login form:

<form method="post" action="/journal/login.php">
  <input name="email" type="email" required placeholder="Email">
  <input name="password" type="password" required placeholder="Password">
  <button type="submit">Login</button>
</form>

Notes & next steps:
- These endpoints return JSON. For browser form submits you may want to redirect after success instead of returning JSON. Replace the JSON responses with header('Location: ...') and exit.
- Add CSRF protection for production.
- Use environment variables or a separate config file (outside webroot) for credentials in production.
- Consider adding email verification and password reset flows.

