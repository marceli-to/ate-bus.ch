# ate-bus.ch — Updates Todo

*Created: 2026-02-04 from client email*
*Last updated: 2026-02-04 17:20*

---

## ✅ Completed by Jarvis

- [x] **Schwarzer Balken entfernen** — Removed black bar from header
- [x] **Typo "Sie"** — Fixed "Wir sind für sie da" → "Wir sind für Sie da" in kontakt.md
- [x] **Herr pre-selection** — Removed default "Herr" selection and test data from ApplicationForm
- [x] **Formular Postleitzahl** — Added numeric-only input (inputmode + pattern + JS filter)
- [x] **Hover-Interaktionen Links** — Added hover color transitions:
  - Article links: Blue-Black → Blue-Primary on hover
  - "Datei hochladen": Blue-Primary → Blue-Black on hover
- [x] **Mobile padding** — Increased container padding from px-12 to px-16
- [x] **Buslinien-Logos** — Scaled down from w-120/160/180 to w-100/120/140
- [x] **Spickel mobile** — Now visible on all screen sizes (was hidden below sm)
- [x] **Hero textbox spacing** — Added sm:mb-100 to prevent overlap on tablet

---

## 🔍 Needs Clarification / Visual Check

- [ ] **Typografie H4** — "Remove spacing after H4" — H4 component has no default margin, couldn't find where issue occurs. Need visual example.
- [x] **Text ersetzen** — "Input should replace placeholder text" — Current implementation looks correct (placeholder disappears on input). Need clarification on what's wrong.
- [ ] **Form text color** — "Blue-Gray → Blue-Black on input" — Input text is already blue-black, placeholder is blue-gray/50. Is this about different contrast?
- [x] **Fundbüro Abstand zum Header** — Need to compare with content pages without images to match spacing

---

## Todo Marcelito

*Needs Figma assets or design decisions*

- [ ] **Spickel angle** — Adjust angle to match logo *(need exact angle or reference)*
- [ ] **Bilder** — Replace images with newly edited versions from Figma
- [x] **Aktuelle Jobangebote CTA** — Change image; remove from Fundbüro (needs new image)
- [ ] **Logos** — Review/replace logos (VBZ pixelated) — needs hi-res assets
- [ ] **Buttons** — Update negative button colors *(need color values)*
- [x] **Menu** — Adjust spacing + text size per Figma
- [x] **Links - Interaktion** — Link footer addresses *(you need to add link field to fieldset)*
- [ ] **Icon Grösse (Über uns)** — Make leaf icon bigger per Figma
- [ ] **Partner und Verbände** — Replace "Aktuelle Jobangebote" section (needs content/layout from Figma)
- [x] **Footer Mobile** — New layout per Figma
- [x] **Micro-Animationen** — "leichtes heranziehen" — need specifics on which elements and timing

---

## Notes

- Footer tel:/mailto: links are already wired up ✓
- Figma is the source of truth for visual changes
- Form test data has been removed — forms now start empty
