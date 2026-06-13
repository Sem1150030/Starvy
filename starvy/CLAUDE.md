# CLAUDE.md — Starvy 🌟

Guidance for working in this repo. Read before generating or editing UI, Blade, or styles.

## Project

Starvy is an AI-powered learning platform built with Laravel. It helps teachers create and manage learning materials and gives students personalized, intelligent study content. **The student-facing UI is aimed at children**, so it should always feel friendly, playful, and encouraging — never clinical or "enterprise."

## Tech stack

- **Backend:** Laravel (PHP) + Livewire
- **UI components:** Flux UI (`<flux:*>` Blade components)
- **Styling:** Tailwind CSS
- **Database:** MySQL / PostgreSQL
- **AI:** OpenAI API
- **Search:** Laravel Scout + Meilisearch

When adding UI, reach for a Flux component first (`flux:button`, `flux:badge`, `flux:avatar`, `flux:heading`, `flux:input`, etc.). Only drop to custom Blade + Tailwind when Flux has no fitting component (e.g. the colorful subject cards).

## Design principles (kid-friendly)

1. **Big and tappable.** Generous padding, large touch targets, nothing cramped.
2. **Rounded everything.** Cards use `rounded-3xl`, icon tiles `rounded-2xl`, pills/buttons `rounded-full`. Avoid sharp corners.
3. **Bright but cohesive.** Use the palette below. Keep one color family per screen; let accents pop.
4. **Friendly motion.** Subtle hover lift (`.pop`) and a gentle floating mascot (`.float`). Keep animation light — too much reads as noisy.
5. **Reward loop.** Always surface progress and rewards: stars ⭐, streaks 🔥, trophies 🏆, progress bars. These motivate kids and should stay visible.
6. **Emoji as friendly anchors** for now, but treat them as placeholders — see "Known gaps."

## Color palette (Ocean)

The current theme is "Ocean cool." Define these as CSS variables / Tailwind tokens; don't scatter raw hex through markup.

| Role | Hex |
|------|-----|
| Ink (text) | `#173A45` |
| Teal (primary/brand) | `#14B8B0` |
| Aqua | `#29B6E8` |
| Seafoam | `#2DD4A8` |
| Ocean blue | `#3B82F6` |
| Muted label | `#6E96A0` |
| Page background | radial glow `#CDEFF0 → #E8F8FA → #FFFFFF` |
| Hero gradient | `linear-gradient(135deg,#22D3C5,#1FA2D6,#2A6FE0)` |

**Subject tiles** run across the water spectrum so they stay distinct. Each is `[background, accent, title, label]`:

- Math — `#DDF5EC` / `#2DD4A8` / `#0A5A45` / `#0F7A5E`
- Reading — `#D6F3F1` / `#14B8B0` / `#0A5751` / `#0E7A72`
- Science — `#DAF1FB` / `#29B6E8` / `#0B4F6B` / `#126C90`
- Art — `#DCEAFE` / `#3B82F6` / `#163E8A` / `#1E56B0`
- Music — `#E4E6FB` / `#6366F1` / `#2E2E8A` / `#3E3EB0`

**Rules for color:**
- Text on a colored card uses a **dark shade of that same color family**, never black or generic gray.
- The reward counters (⭐ gold, 🔥 orange) are intentionally warm to pop against the cool palette. Keep that contrast.
- To re-theme, change the variables and the subject array in one place — never hardcode a new palette inline.

## Typography

- **Display / headings:** `Fredoka` (rounded, playful). Apply via the `.display` class.
- **Body:** `Nunito`.
- Load both from Google Fonts.
- Use sentence case for headings and labels — never Title Case or ALL CAPS.

## Reusable patterns

Keep these helper classes consistent across views:

```css
.display { font-family: 'Fredoka', sans-serif; }
.pop     { transition: transform .15s ease; }
.pop:hover { transform: translateY(-4px) rotate(-.5deg); }
.float   { animation: float 3.5s ease-in-out infinite; }
@keyframes float { 0%,100%{transform:translateY(0) rotate(-6deg)} 50%{transform:translateY(-10px) rotate(6deg)} }
```

- **Cards:** white or tinted background, `rounded-3xl`, `shadow-sm`, `.pop` on interactive ones.
- **Icon tiles:** `w-14 h-14 rounded-2xl` with the subject accent as background.
- **Progress bars:** track `h-2.5 rounded-full bg-black/5`, fill uses the subject accent.
- **Buttons:** `rounded-full`, `.display` font. When overriding a `flux:button`, use `!`-prefixed utilities (`!rounded-full`, `!px-7`) so Flux's defaults are beaten.

## Blade & Livewire conventions

- Prefer **Livewire components** for anything dynamic (progress, stars, lesson lists) — don't hardcode values that should come from the database. The current views use placeholder data; replace it with real bindings.
- Keep repeated UI (subject cards, stat chips) in `@foreach` loops driven by an array or collection, not copy-pasted markup.
- Extract shared chrome (top bar, layout) into Blade components once there's more than one screen.
- Wrap pages in the app layout; include `@fluxAppearance` in `<head>` and `@fluxScripts` before `</body>`.

## Code style

- Format PHP with **Laravel Pint** before committing.
- Follow Laravel naming: `PascalCase` models, `camelCase` methods, `snake_case` columns, `kebab-case` Blade views.
- Keep controllers thin; put logic in actions, services, or Livewire components.
- Write tests (Pest) for new backend behavior.

## Content & voice

- Speak to a child: warm, simple, encouraging. Short sentences, plain verbs.
- Buttons say what happens: "Continue learning," "Start lesson" — not "Submit."
- Empty and error states are kind and tell the child what to do next, not blame them.
- Keep an action's name consistent through its whole flow.

## Accessibility

- Maintain readable contrast — the dark-shade-on-tint rule above handles most of it; check any new color pairing.
- Visible keyboard focus on all interactive elements.
- Respect `prefers-reduced-motion`: disable `.float` and hover transforms when set.
- Emoji used as meaningful icons need an accessible label; purely decorative ones should be hidden from screen readers.

## Known gaps / TODO

- **Emoji are placeholders.** They render differently across devices; plan to replace subject/mascot emoji with a consistent custom icon set or illustrations.
- **Dark mode** is not yet defined for the kid theme.
- **Student data is mocked.** Stars, streak, and progress are currently hardcoded in the views — replace with real Livewire bindings backed by the database.
- **Ocean theme is not wired up yet.** `resources/css/app.css` still ships the starter-kit defaults (`Instrument Sans`, zinc palette). The palette, fonts, and helper classes above describe the target system to build toward, not the current state.

## Styling skill

When generating or editing kid-facing UI, use the **`starvy-styling`** skill (`.claude/skills/starvy-styling/`). It turns the design principles above into a concrete checklist and copy-paste patterns.
