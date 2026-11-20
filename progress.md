# Progress

## Open: Fundmeldung + Reply-To (client request 2026-11-17)

Status: **built on branch `feature/fundmeldung`, tested locally, not merged.** Waiting for the client's OK on the rebuild (mail sent 2026-11-17).

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
- Test on production: the old link `/fundmeldung.php?k=…` redirects; one test report with a photo; check the entry and photo in the CP.
- Send the client the new link `https://www.ate-bus.ch/fundmeldung?k=<code>`.

### Open points
- Not yet checked in the CP UI: entry list columns, photo thumbnails from the private container.
- Mail content is untrusted input for the client's Claude automation (prompt injection). Tell the client; it applies to «Verlust melden» too.
- Local test entries exist in `found_items` (2 entries, 2 photos). Delete them before checking the CP if they're in the way.
