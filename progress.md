# Progress

## Open: Fundmeldung + Reply-To (client request 2026-11-17)

Status: **waiting for client reply** (mail sent 2026-11-17). Code is on branch `feature/fundmeldung` (pushed, not merged).

### Request
1. Hidden staff page «Fundmeldung» (found items, not lost): drivers and cleaning staff report found items by phone, with up to 2 photos. Link only in the Infosoft app: `https://www.ate-bus.ch/fundmeldung.php?k=8307`.
   - Without a valid code the page shows «404 Not Found». Not linked anywhere, no sitemap, noindex.
   - Mail to `fundgegenstaende@ate-bus.ch`, from `noreply@ate-bus.ch`, photos as attachments.
   - **Do not change** field names, table layout or subject («Neue Fundmeldung: …»). The client parses these mails automatically (with Claude).
2. Reply-To on «Verlust melden» and Bewerbungen: Reply-To = the e-mail entered in the form. Sender stays noreply@, subject and body unchanged.

### Done (local, uncommitted)
- `app/Mail/LostAndFoundNotification.php`, `app/Mail/ApplicationNotification.php`: `replyTo` set to the form's name and e-mail. Tested in tinker. **Can ship independently.**
- `public/fundmeldung.php`: client's file, unchanged except that sending uses the Laravel mailer instead of PHP `mail()`. The file boots Laravel itself for this. If sending fails, the page shows the client's error message and the error is logged. Tested with `php -S` + `MAIL_MAILER=log`: 404 without the code, 200 with it, mail correct with the attachment.
- `LocalValetDriver.php`: Herd sends every `.php` request through `index.php`, so this driver makes Herd execute `public/fundmeldung.php` directly (local only, the server doesn't use it).

### Proposed to the client: rebuild in the website
Client offered «Falls es für euch sauberer ist, dürft ihr sie gerne als Statamic-Formular nachbauen». We proposed:
- Laravel controller + Statamic collection, like `LostAndFoundController`. Each report is saved in the control panel (incl. photos), then mailed. **The mail stays identical.**
- New URL `/fundmeldung?k=…`. The old `public/fundmeldung.php` becomes a 302 redirect that keeps the query string (`?k=…`).
- Longer access code in `.env` (`8307` is their postcode and appears in the page footer), rate limit, validate «Gegenstand» against the list.
- Lines (VBZ/VBG) and item categories editable in the CP.
- Estimate: 3–5 h.

### Security notes (from review)
- `8307` is guessable and can be brute-forced (4 digits, no rate limit).
- Mails are processed automatically by Claude, so free-text fields are untrusted input (prompt injection). The client should know this; it applies to «Verlust melden» too.
- The standalone file runs outside Laravel's error handling, so `display_errors` must be off in production.
- Production nginx/Apache config hasn't been seen. Check that `/fundmeldung.php` executes there.

### Next
- **Client agrees to the rebuild:** build it as above, turn `public/fundmeldung.php` into the redirect, test on production with a test report incl. a photo.
- **Client declines:** ship `public/fundmeldung.php` as it is (recommend a longer code), test on production.
- Either way: ship the Reply-To change. Decide whether `LocalValetDriver.php` goes into git or `.gitignore`.
