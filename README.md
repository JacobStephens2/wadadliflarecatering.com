This is the website for Wadadli Flare Catering, accessible at https://wadadliflarecatering.com/.

See [DEPLOYMENT.md](DEPLOYMENT.md) for the server-side setup git cannot track:
`private/.env` permissions (a security requirement, not a preference), the
directories the review refresh needs to write, and the systemd units that keep
Google reviews current.

Fast Lead intake and Mail transport regression checks (PHP 8.3 with mbstring,
Node.js; no MySQL, Composer, credentials or network access required):

```bash
php tests/lead_intake.php
php tests/email.php
node tests/submit-button.cjs
```

End-to-end form regression checks (PHP 8.3 with PDO MySQL and mbstring, plus
MySQL/MariaDB server binary at `/usr/sbin/mysqld`):

```bash
php tests/forms.php
```

`tests/forms.php` initializes a disposable MySQL/MariaDB instance with the
`master` schema, preserves an existing lead through the migration, and runs
both form POST pipelines against an isolated temporary rate-limit directory. It
stops its server and removes its temporary data on exit. SMTP and Turnstile are
replaced only at their external boundaries; no production credentials are loaded
and no emails are delivered. To run one scenario, pass `legitimate`,
`migration`, `flagged`, `rejections`, `validation`, `duplicates`, `nul-date`,
`replayed-token`, or `db-outage`.

Pan menu regression checks (PHP 8.3 with DOM support; no Composer, database,
credentials or network access required):

```bash
php tests/menu.php
```

Checks both rendered Pan menus and all three page callers for complete card
order, pan sizes, prices, heading levels, escaping and related links. Page
checks run in isolated PHP subprocesses with `ENV_LOADED` defined before the
site bootstrap, an empty environment, remote file access disabled, and mail
and network functions disabled.
