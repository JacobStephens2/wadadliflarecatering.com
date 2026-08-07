# Browser agent task: create a Places API (New) key for wadadliflarecatering.com

You are operating a browser inside Jacob's Google account. Produce one restricted
Google Cloud API key that can call the Places API (New), and report it back.

Work through the steps in order. Each has a success check - do not advance until
it passes. If a check fails twice, stop and report rather than improvising.

## Constraints

- **Enable "Places API (New)", not "Places API".** These are two different
  products with different endpoints. The server calls
  `https://places.googleapis.com/v1/places/...`, which only the New one serves.
  Enabling the legacy "Places API" produces a key that returns 403 forever. This
  is the single most likely way to get this task wrong.
- **Restrict by IP address, not HTTP referrer.** The call is server-side from a
  cron job, so there is no browser referrer to match. A referrer restriction
  produces `REQUEST_DENIED`.
- **Do not modify or delete existing API keys, projects, or billing accounts.**
  Create new things only. Other services in this account depend on them.
- **The key is a credential.** Return it only in your final report to Jacob. Do
  not paste it into any web form, document, search box, or third-party site.
- Do not enable any API beyond Places API (New).

## Step 1 - Confirm the account

Go to https://console.cloud.google.com

**Check:** the account avatar (top right) shows a Google account Jacob controls.
If it shows an unexpected account, or you land on a sign-in screen you cannot
complete, stop and report which account is active.

## Step 2 - Select or create a project

Open the project picker in the top bar.

- If a project for this site already exists (something like
  `wadadli-flare-catering`), select it.
- Otherwise go to https://console.cloud.google.com/projectcreate and create one
  named `wadadli-flare-catering`. Leave organization/location at their defaults.
  Creation takes 10-30 seconds.

**Check:** the top bar shows the intended project name. Record the **project ID**
(shown on the project picker or the dashboard - it may have a numeric suffix,
e.g. `wadadli-flare-catering-472913`). You need it for the report.

## Step 3 - Confirm billing is attached

Go to https://console.cloud.google.com/billing/linkedaccount and confirm the
selected project is linked to an active billing account.

Google refuses to enable Places API on a project with no billing account, even
though this usage sits inside the free allowance (~730 calls/month against a
5,000-call free tier).

- If billing is already linked: continue.
- If not, and an existing billing account is available: link it.
- If no billing account exists at all: **stop and report.** Creating one requires
  entering payment details, which is Jacob's decision, not yours.

**Check:** the page shows a linked, active billing account for this project.

## Step 4 - Enable Places API (New)

Go directly to:

https://console.cloud.google.com/apis/library/places.googleapis.com

That URL is the New API specifically - it avoids the search results, where
"Places API" and "Places API (New)" sit next to each other and are easy to
confuse. Confirm the page heading reads **Places API (New)** before clicking
anything.

Click **Enable**. Wait for it to finish (up to ~30 seconds).

**Check:** the button now reads **Manage** rather than **Enable**, or the page
redirects to an API management view. Then confirm at
https://console.cloud.google.com/apis/dashboard that **Places API (New)** appears
in the enabled list.

If the heading said only "Places API" without "(New)", you are on the wrong page.
Go back to the URL above.

## Step 5 - Create the API key

Go to https://console.cloud.google.com/apis/credentials

Click **+ Create credentials** → **API key**. A dialog shows the new key.

Copy the key (starts with `AIza`). Keep it for the final report.

Do not dismiss the dialog before copying. If you lose it, the key is still
listed on the credentials page and can be revealed with **Show key**.

**Check:** a new key exists in the API Keys list.

## Step 6 - Restrict the key

Still on the credentials page, click the new key's name to edit it.

1. **Name:** set to `wadadliflarecatering-places` so it is identifiable later.

2. **Application restrictions:** select **IP addresses**. Click
   **Add an item** and enter exactly:

   ```
   68.183.62.24
   ```

   That is the droplet that makes the call. Select IP addresses, not "None" and
   not "Websites" / HTTP referrers.

3. **API restrictions:** select **Restrict key**, open the dropdown, and check
   **Places API (New)** only. If the dropdown does not list it, Step 4 did not
   complete - go back and finish it.

4. Click **Save**.

**Check:** reopening the key shows Application restrictions = IP addresses with
`68.183.62.24` listed, and API restrictions = Places API (New).

## Step 7 - Wait for propagation

New keys and restriction changes take up to 5 minutes to take effect. Note the
time you saved the restrictions.

## Report back

Return exactly this, filled in:

```
Project ID:      <project id>
Project name:    <project name>
Billing:         linked / not linked
Places API (New) enabled: yes / no
API key:         AIza...
Key name:        wadadliflarecatering-places
IP restriction:  68.183.62.24
API restriction: Places API (New)
Restrictions saved at: <UTC time>
Problems hit:    <anything unexpected, or "none">
```

Do not attempt to test the key yourself - there is no browser-reachable test
that respects an IP restriction, and a failed browser test would be a false
negative. Verification happens server-side with:

```
sudo -u www-data php /var/www/wadadliflarecatering.com/private/refresh-google-reviews.php
```

## If something goes wrong

| Symptom | Cause | Action |
| --- | --- | --- |
| Enable button greyed out | No billing account linked | Go back to Step 3 |
| "Places API (New)" missing from the API restrictions dropdown | Step 4 did not complete | Re-enable, wait, reload the credentials page |
| Landed on a "Places API" page with no "(New)" | Wrong product | Use the Step 4 URL verbatim |
| Asked to create a billing account | None exists | Stop and report - Jacob decides |
| Two-factor / recovery prompt | Account security | Stop and report - do not attempt to bypass |
