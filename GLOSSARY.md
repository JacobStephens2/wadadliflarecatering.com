# Glossary

**Lead intake** — unified Contact and Quote submission handling behind one
seam: anti-spam guard rendering, request parsing, validation, locked
self-purging rate limits, 10-minute MySQL deduplication, combined spam
flagging, persistence, and notification assembly wired to external Turnstile
and Mail transport ports.

**Mail transport** — single-purpose outbound email delivery adapter using
PHPMailer SMTP when available and falling back to RFC-quoted `mail()` with
injected SMTP configuration and no spam rules or form templates.

**Pan menu** — an ordered selection of dishes with displayed names and
prices for their offered pan sizes; the current selections are Starches and
Hot Sides.

**Reviews** — the site's review list and Google rating summary behind one
seam: curated archive, harvest store, and live cache merged newest-first
into display-ready records, plus the hourly refresh that fetches, caches,
captures, and reports the unseen-review gap, wired to an external Google
Places port.
