This is the website for Wadadli Flare Catering, accessible at https://wadadliflarecatering.com/.

See [DEPLOYMENT.md](DEPLOYMENT.md) for the server-side setup git cannot track:
`private/.env` permissions (a security requirement, not a preference), the
directories the review refresh needs to write, and the systemd units that keep
Google reviews current.

Form regression checks (PHP 8.3 with PDO MySQL and mbstring, MySQL server binary
at `/usr/sbin/mysqld`, Node.js, and `composer install`):

```bash
php tests/email.php
php tests/forms.php
node tests/submit-button.cjs
```

`tests/forms.php` initializes a disposable MySQL instance with the `master`
schema, preserves an existing lead through the migration, and runs both form
POST pipelines. It stops its server and removes its temporary data on exit.
SMTP and Turnstile are replaced only at their external boundaries; no production
credentials are loaded and no emails are delivered. To run one scenario, pass
`legitimate`, `migration`, `flagged`, `rejections`, `validation`, or `duplicates`.
