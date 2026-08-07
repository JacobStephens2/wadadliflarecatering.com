# Deployment notes

Server-side state that git cannot hold. Git tracks file contents and the
executable bit, not ownership, permissions, gitignored files, or anything under
`/etc`. Everything below has to be recreated by hand on a fresh deploy, and the
first item is a security requirement, not a preference.

Current host: DigitalOcean droplet, public IP `68.183.62.24`. Apache serves
`public/` as the DocumentRoot with PHP 8.3 via php-fpm running as `www-data`.

## private/.env - must not be world-readable

```
-rw-r-----  jacob:www-data   private/.env
```

```bash
sudo chown jacob:www-data private/.env
sudo chmod 640 private/.env
```

This file holds `DB_PASS`, `SMTP_PASS`, `TURNSTILE_SECRET`, and
`GOOGLE_PLACES_API_KEY`. It shipped as `664 jacob:jacob`, meaning every local
account on the droplet could read all four. `640 jacob:www-data` is the tightest
mode that still works: `www-data` needs read access because php-fpm and the
review refresh both run as it.

Do **not** use the `web_app_editors` group here even though `www-data` belongs to
it. That group also contains `ezra`, `mysql`, and `conductor`, so it would widen
access rather than close it.

A fresh deploy will recreate this file at whatever the umask gives, which is
world-readable on this host. Re-apply the two commands above.

Required variables:

| Variable | Notes |
| --- | --- |
| `DB_HOST` `DB_NAME` `DB_USER` `DB_PASS` | MySQL, see `private/schema.sql` |
| `SMTP_HOST` `SMTP_PORT` `SMTP_USER` `SMTP_PASS` `SMTP_ENCRYPTION` `SMTP_FROM_EMAIL` `SMTP_FROM_NAME` | Resend. **`SMTP_PASS` is fleet-managed - never set it by hand.** See below. |
| `TURNSTILE_SITE_KEY` `TURNSTILE_SECRET` | Cloudflare Turnstile on the contact and quote forms |
| `GOOGLE_PLACES_API_KEY` | Google reviews. Key `wadadliflarecatering-places`, project `wadadli-flare-catering`, restricted to Places API (New) and IP `68.183.62.24` |

### SMTP_PASS is rotated fleet-wide

The single source of truth is `/etc/shared-secrets/smtp.env.sops`, rotated with
`rotate-secret smtp`. This file is already wired into
`/usr/local/bin/refresh-smtp-derived` (the `SMTP_PASS=` rewrite), so rotation
reaches it automatically. Never paste a Resend key in by hand: a private copy
drifts from the shared one and starts returning 535 at the next rotation.

`refresh-smtp-derived` rewrites the file with a truncate-write that preserves the
inode, so the `640 jacob:www-data` mode above survives rotation. Do not change
that script to use `mv`, which would replace the inode and silently reset the
mode to the root umask.

## Writable directories

The hourly review refresh runs as `www-data` and writes both of these. Neither
is created by a git checkout.

```bash
sudo install -d -o www-data -g www-data        -m 775 private/cache
sudo install -d -o www-data -g web_app_editors -m 775 private/data
```

- `private/cache/` - gitignored, disposable. Holds `google-reviews.json`, the
  last Places API response. Deleting it costs nothing; the next refresh rebuilds
  it and the site falls back to the archive meanwhile.
- `private/data/` - **tracked in git, not disposable.** Holds
  `captured-reviews.json`, the only durable copy of Google reviews that have
  since dropped out of the API's 5-review window. Losing it loses those reviews
  permanently. Group is `web_app_editors` so `jacob` can commit the file that
  `www-data` writes.

## systemd units

Both live in `/etc/systemd/system/` and are outside this repo.

`wadadli-reviews-refresh.service`:

```ini
[Unit]
Description=Refresh cached Google reviews for wadadliflarecatering.com
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
User=www-data
Group=www-data
ExecStart=/usr/bin/php /var/www/wadadliflarecatering.com/private/refresh-google-reviews.php
SuccessExitStatus=0
```

`wadadli-reviews-refresh.timer`:

```ini
[Unit]
Description=Refresh wadadliflarecatering.com Google reviews hourly

[Timer]
OnBootSec=5min
OnUnitActiveSec=1h
Persistent=true
AccuracySec=1min

[Install]
WantedBy=timers.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now wadadli-reviews-refresh.timer
```

Hourly rather than daily on purpose. The Places API returns 5 reviews ranked by
relevance, so a review can sit in that window briefly and rotate out. More
frequent sampling means more chances to capture one before it disappears. At
roughly 730 calls a month this stays inside the free tier.

## Verification

```bash
# Reviews refresh, including the .env read and the Places API call
sudo -u www-data php private/refresh-google-reviews.php

# Same thing through systemd, which is how it actually runs
sudo systemctl start wadadli-reviews-refresh.service
journalctl -u wadadli-reviews-refresh.service -n 20

# .env is readable by the web server and nobody else
sudo -u www-data test -r private/.env && echo ok
sudo -u ezra     test -r private/.env && echo "STILL WORLD-READABLE"
```

Pages returning 200 does not prove `.env` was read - most of the site renders
fine without it. Load `/quote-request` and confirm a `data-sitekey` attribute is
present instead. That value comes from `TURNSTILE_SITE_KEY`, so it only appears
if the file was read successfully.
