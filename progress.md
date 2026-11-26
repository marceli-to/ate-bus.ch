# Progress

## Open: Unfallmeldung (client request 2026-11-26)

Status: **merged into `master` 2026-11-26, not deployed.** Next: staging, then the client tests the flows on the phone.

### Request
Digital accident report approved by the management, delivered as a Statamic package (static `app.js` generated from their prototype). Pages hidden like the Fundmeldung:
- `/unfallmeldung` staff (vehicle, passenger, staff accident), `/unfall-fahrgast` passengers (QR code on the business card), `/b` other party types in the code.
- Mails: vehicle/passenger → `info@`, staff → `personal@`, copy to the person involved (subject «Kopie: …», Reply-To info@).

### Built
- Rewritten in Vue in the site's design (user's decision), same steps, questions, texts and report as the prototype: `resources/js/components/accident-report/` (lazy-loaded chunk, only on these pages). All code English, all user-facing text German.
- The report is one Vue component (`report/AccidentReportDocument.vue`, inline styles only): preview before signing, success page and mail body. Drawings go to the mail as PNG (Outlook), photos as attachments.
- Server: `AccidentReportController` (pages, submit), `OtherPartyController` (QR flow «jeder auf seinem Handy»: code in the cache 12 h, photos wait in `other-party/{CODE}` and move into the report on submit), `ReportHtml` (whitelist sanitizer, cid images, `Unfallbericht.html`), `AccidentReportNotification`.
- `/unfallmeldung` needs `?k=ACCIDENT_REPORTS_CODE` (else 404). `/unfall-fahrgast` cannot have a code (URL printed on the card) → only noindex/unlinked. Other party is protected by the 6-character code.
- Reports saved as entries in collection **Unfallmeldungen** (`accident_reports`, CP under Formulare) + private asset container `unfallmeldungen` (disk `accident_reports`). Mail failure → entry with «Mail versendet: aus», page tells staff to call the Pikett.
- Fleet and lines/stops: `resources/data/accident-reports/vehicles.json`, `lines.json` (from the client's `daten.js`, English keys).
- `accident-reports:prune` (daily 03:15) deletes entries + files after `ACCIDENT_REPORTS_RETENTION_DAYS` (365) and abandoned QR uploads after 2 days.
- Throttles have their own prefixes: with plain `throttle:x,y` all routes share one counter per IP, and the 3-second status polling would have blocked the submit.

### Deploy checklist (test server first)
- `.env`: `ACCIDENT_REPORTS_CODE=<long random code>`; optional `ACCIDENT_REPORTS_EMAIL` (info@), `ACCIDENT_REPORTS_STAFF_EMAIL` (personal@), `ACCIDENT_REPORTS_RETENTION_DAYS`. On a test server point the two addresses at a test inbox.
- PHP: `upload_max_filesize = 12M`, `post_max_size = 64M`, `max_file_uploads = 50`; nginx `client_max_body_size 64M`.
- Scheduler (cron `schedule:run`) must run; cache driver must persist (file/redis, not array).
- Open the CP once (creates collection + container), then give the right role «view» on collection and container Unfallmeldungen. Staff reports contain health data: only HR should see them.
- Send the client the staff link `…/unfallmeldung?k=<code>` for the Infosoft app.

### Open points
- `/unfall-fahrgast` is public: anyone can trigger a «copy» mail to any address (throttled 12 per 10 min per IP). Same as the prototype; tell the client.
- Fleet/line changes need a code change (JSON files). Could move to the CP later if they change often.
- Local test: code `8307` in local `.env`; e2e scripts were run with Playwright (not in the repo).

## Done: Fundmeldung + Reply-To (client request 2026-11-17)

Status: **live on prod** (confirmed 2026-11-26).

### Request
1. Hidden staff page «Fundmeldung» (found items, not lost): drivers and cleaning staff report found items by phone, with up to 2 photos. Link only in the Infosoft app.
   - Without a valid code the page shows 404. Not linked anywhere, no sitemap, noindex.
   - Mail to `fundgegenstaende@ate-bus.ch`, from `noreply@ate-bus.ch`, photos as attachments.
   - **Do not change** field names, table layout or subject («Neue Fundmeldung: …»). The client parses these mails automatically (with Claude).
2. Reply-To on «Verlust melden» and Bewerbungen: Reply-To = the e-mail entered in the form. Sender stays noreply@, subject and body unchanged.

### Built (branch `feature/fundmeldung`)
- **Reply-To:** `app/Mail/LostAndFoundNotification.php`, `app/Mail/ApplicationNotification.php`.
- **Fundmeldung as part of the site** (instead of the client's standalone file):
  - Route `GET|POST /fundmeldung?k=CODE` → `FoundItemController`. Throttled: GET 60/min, POST 30 per 10 min per IP. No CSRF check for this route (the link code protects it, and a page left open in the app would otherwise get 419).
  - Page in the site's own style: Antlers template `templates/fundmeldung` in the thin layout `templates/layout-thin` (logo only, one-line footer, no menu). Form = Vue `FoundItemForm.vue`, built from the site's `Form*` components plus two new ones: `FormChoice.vue` (big option buttons) and `FormPhoto.vue` (camera, preview, shrinks to 1600px JPEG in the browser). Name and role are remembered in localStorage. `store` returns JSON with errors per field (422).
  - Mail `app/Mail/FoundItemNotification.php`: HTML built 1:1 from their file (verified byte-identical against their algorithm). Subject is the same. Sender `ATE Bus Fundmeldung <mail.from.address>`.
  - Each report is saved first as an entry in the collection **Fundmeldungen** (`found_items`), then mailed. Toggle «Mail versendet» = off means the mail failed and the report exists only in the CP.
  - Photos go to the **private** asset container `fundmeldungen` (disk `found_items` → `storage/app/private/fundmeldungen`, no public URL).
  - Lines come from the collection **Buslinien** (`bus_routes`, partner vbg/vbz). Item categories come from the global set **Fundmeldung** (list field, defaults = the client's list).
  - `app/Support/FoundItems.php` creates the collection, container and global set on the first report, because `content/` is not in git.
  - `public/fundmeldung.php` = 302 redirect to `/fundmeldung` keeping `?k=…` (old link from the client's mail).
- Shared form components (`FormInput`, `FormSelect`, `FormTextarea`, used by all forms): 16px text on mobile (stops iPhone zoom on focus), same field height for inputs and selects.
- **Follow-ups (2026-11-19/20):** no automatic confirmation mail to the reporter on «Verlust melden» any more (they answer via Claude, using Reply-To); `LostAndFoundConfirmation` stays in the code but is no longer sent. The «Gesucht/Gefunden» subject prefix was asked for, then withdrawn on 2026-11-20: subjects are again «Neue Verlustmeldung: <Name>» and «Neue Fundmeldung: …».
- **Mail format the client's Claude automation expects (2026-11-20 spec), all met:** both forms to `fundgegenstaende@ate-bus.ch` (now also the default for `LOST_AND_FOUND_EMAIL`); Fundmeldung subject/fields/photo attachments as built; Verlustmeldung subject «Neue Verlustmeldung: <Name>», fields Name, E-Mail, Telefon, Datum, Uhrzeit, Buslinie, Beschreibung; date TT.MM.JJJJ, time HH:MM (Verlustmeldung time now validated as HH:MM).
- Server-side checks (per field, shown at the field) on top of the client's own: Gegenstand must be in the list, Abgabe must be one of 4 options, date/time format, field lengths, real image type (jpeg/png/webp/heic/heif), max 8 MB per photo.

### Deploy checklist
- `.env` on the server: `FOUND_ITEMS_CODE=<long random code>` (without it the page is always 404). Optional: `FOUND_ITEMS_EMAIL` (default `fundgegenstaende@ate-bus.ch`).
- Server `.env`: `LOST_AND_FOUND_EMAIL=fundgegenstaende@ate-bus.ch` (or remove the line, the default is that address). Check what it is set to now.
- Check that `MAIL_FROM_ADDRESS` on the server is `noreply@ate-bus.ch`. The Fundmeldung uses it as sender.
- Client adds the missing lines in CP → Buslinien: **VBG 653, VBG 654, VBZ 702**. New toggle «Nur intern» on a line: it shows in the Fundmeldung but not in the «Verlust melden» dropdown (`/api/bus-routes`). The public lines page only shows lines picked by hand in the page builder, so new lines never appear there on their own. Ask the client whether these 3 lines should be internal.
- After deploy, open the CP once: that creates the empty collection, photo container and global set «Fundmeldungen» (`AppServiceProvider`). Then in CP → Berechtigungen give the editor role (whoever has Bewerbungen/Fundbüro) «view» on collection Fundmeldungen **and** asset container Fundmeldungen (otherwise no photos). Roles/users are not in git, so check on the server.
- Test on production: the old link `/fundmeldung.php?k=…` redirects; one test report with a photo; check the entry and photo in the CP.
- Send the client the new link `https://www.ate-bus.ch/fundmeldung?k=<code>`.

### Open points
- Bus number pre-selects the group (10–40 VBZ, 54–78 VBG, 53 neither), taken from the client's file and hard-coded in `FoundItemForm.vue`. Staff can override it. Needs a code change if the fleet numbering changes.
- Local mail goes to MailHog (http://127.0.0.1:8025) via `.env` SMTP 127.0.0.1:1025.
- Not yet checked in the CP UI: entry list columns, photo thumbnails from the private container.
- Mail content is untrusted input for the client's Claude automation (prompt injection). Tell the client; it applies to «Verlust melden» too.
- Local test entries exist in `found_items` (2 entries, 2 photos). Delete them before checking the CP if they're in the way.
