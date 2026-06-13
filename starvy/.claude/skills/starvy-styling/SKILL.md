---
name: starvy-styling
description: Use when generating or editing any student-facing UI, Blade view, Flux component, or Tailwind/CSS in the Starvy learning platform — the kid-friendly "Ocean" design system. Triggers include building dashboards, subject cards, lesson screens, buttons, progress bars, reward counters (stars/streaks), or any markup a child will see.
---

# Starvy styling — kid-friendly Ocean theme

## Overview

Starvy's student UI is for **children**: friendly, playful, encouraging — never clinical or "enterprise." This skill turns the design principles in `CLAUDE.md` into a concrete checklist and copy-paste patterns. Read `CLAUDE.md` for the full rationale; this is the working reference.

**Core rule:** reach for a Flux component first (`flux:button`, `flux:badge`, `flux:avatar`, `flux:heading`, `flux:input`). Drop to custom Blade + Tailwind only when Flux has no fit (e.g. colorful subject cards).

## Pre-flight checklist

Before writing kid-facing markup, confirm each:

- [ ] **Flux first** — is there a `flux:*` component for this? Use it before hand-rolling.
- [ ] **Rounded** — cards `rounded-3xl`, icon tiles `rounded-2xl`, buttons/pills `rounded-full`. No sharp corners.
- [ ] **Tokens, not hex** — pull colors from CSS variables / the subject array. Never paste raw hex into markup.
- [ ] **One color family per screen** — pick a subject family; let warm reward counters pop against it.
- [ ] **Dark-shade-on-tint** — text on a colored card uses a dark shade of *that same family*, never black or gray.
- [ ] **Reward loop visible** — surface stars ⭐, streaks 🔥, trophies 🏆, or a progress bar where it fits.
- [ ] **Loop, don't repeat** — repeated cards/chips come from a `@foreach` over an array/collection.
- [ ] **Dynamic = Livewire** — anything from the DB (progress, stars, lessons) binds to a Livewire component, not hardcoded.
- [ ] **Sentence case** — headings and labels in sentence case. Never Title Case or ALL CAPS.
- [ ] **Friendly verbs** — buttons name the action ("Start lesson", "Continue learning"), never "Submit".
- [ ] **a11y** — keyboard focus visible; `prefers-reduced-motion` disables `.float`/`.pop`; meaningful emoji get a label, decorative ones are `aria-hidden`.

## Color reference

| Role | Hex |
|------|-----|
| Ink (text) | `#173A45` |
| Teal (primary/brand) | `#14B8B0` |
| Aqua | `#29B6E8` |
| Seafoam | `#2DD4A8` |
| Ocean blue | `#3B82F6` |
| Muted label | `#6E96A0` |

**Subject tiles** — `[background, accent, title, label]`:

| Subject | bg | accent | title | label |
|---------|----|--------|-------|-------|
| Math | `#DDF5EC` | `#2DD4A8` | `#0A5A45` | `#0F7A5E` |
| Reading | `#D6F3F1` | `#14B8B0` | `#0A5751` | `#0E7A72` |
| Science | `#DAF1FB` | `#29B6E8` | `#0B4F6B` | `#126C90` |
| Art | `#DCEAFE` | `#3B82F6` | `#163E8A` | `#1E56B0` |
| Music | `#E4E6FB` | `#6366F1` | `#2E2E8A` | `#3E3EB0` |

Reward counters are warm on purpose: ⭐ gold, 🔥 orange — keep that contrast against the cool palette.

## Helper classes

These live in the global stylesheet — use the classes, don't redefine them inline:

```css
.display { font-family: 'Fredoka', sans-serif; }   /* headings + buttons */
.pop     { transition: transform .15s ease; }
.pop:hover { transform: translateY(-4px) rotate(-.5deg); }
.float   { animation: float 3.5s ease-in-out infinite; }
@keyframes float { 0%,100%{transform:translateY(0) rotate(-6deg)} 50%{transform:translateY(-10px) rotate(6deg)} }

@media (prefers-reduced-motion: reduce) {
  .float { animation: none; }
  .pop:hover { transform: none; }
}
```

Body text is `Nunito`; headings/buttons get `.display` (`Fredoka`).

## Canonical patterns

**Subject card** (custom Blade — looped, dark-on-tint, `.pop` for tappable):

```blade
@foreach ($subjects as $subject)
    <a href="{{ $subject['url'] }}"
       class="pop block rounded-3xl shadow-sm p-6"
       style="background: {{ $subject['bg'] }}">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl"
             style="background: {{ $subject['accent'] }}">
            <span aria-hidden="true">{{ $subject['emoji'] }}</span>
        </div>
        <h3 class="display mt-4 text-lg" style="color: {{ $subject['title'] }}">
            {{ $subject['name'] }}
        </h3>
        <p class="text-sm" style="color: {{ $subject['label'] }}">
            {{ $subject['lessons'] }} lessons
        </p>
        {{-- progress bar: track neutral, fill = subject accent --}}
        <div class="mt-4 h-2.5 rounded-full bg-black/5">
            <div class="h-full rounded-full"
                 style="width: {{ $subject['progress'] }}%; background: {{ $subject['accent'] }}"></div>
        </div>
    </a>
@endforeach
```

> Inline `style` is shown here for clarity of the per-subject tokens. Prefer Tailwind tokens / CSS variables once the palette is wired into `@theme`; never scatter raw hex literals — drive them from the `$subjects` array.

**Button** (override Flux defaults with `!`-prefixed utilities so they win):

```blade
<flux:button variant="primary" class="!rounded-full !px-7 display">
    Start lesson
</flux:button>
```

**Reward chip** (warm, always visible):

```blade
<span class="display inline-flex items-center gap-1 rounded-full px-3 py-1 bg-amber-100 text-amber-700">
    <span aria-hidden="true">⭐</span> {{ $stars }}
</span>
```

## Common mistakes

| Mistake | Fix |
|---------|-----|
| Hand-rolling a button/badge/input | Use the `flux:*` component first |
| Raw hex pasted into class/style | Drive from CSS variables or the `$subjects` array |
| Black or gray text on a colored card | Dark shade of that card's own color family |
| Copy-pasted card markup | `@foreach` over an array/collection |
| Hardcoded stars/streak/progress | Bind to a Livewire component (DB-backed) |
| Sharp corners | `rounded-3xl` / `rounded-2xl` / `rounded-full` |
| "Submit" / Title Case / ALL CAPS | Friendly verb, sentence case |
| Flux defaults beating your rounding | `!`-prefixed utilities (`!rounded-full`) |
| Motion with no reduced-motion fallback | Gate `.float`/`.pop` behind `prefers-reduced-motion` |
| Decorative emoji read by screen readers | `aria-hidden="true"`; meaningful ones get a label |

## Re-theming

To change the look, edit the CSS variables and the subject array in **one place** — never introduce a new palette inline in a view.
