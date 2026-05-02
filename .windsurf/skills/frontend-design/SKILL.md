---
name: frontend-design
description: Create distinctive, production-grade frontend interfaces with high design quality. Use when building web components, pages, or full UI flows. Generates creative, polished code that avoids generic AI aesthetics.
license: Complete terms in LICENSE.txt
---

Build production-grade interfaces with a deliberate, memorable aesthetic. Not generic AI output — something that looks genuinely designed.

## Design Thinking (Before Coding)

Understand the context and commit to a **BOLD aesthetic direction**:
- **Purpose** — What problem does this UI solve? Who uses it?
- **Tone** — Pick an extreme and execute it with precision: brutally minimal, maximalist chaos, retro-futuristic, organic/natural, luxury/refined, playful/toy-like, editorial/magazine, brutalist/raw, art deco/geometric, soft/pastel, industrial/utilitarian. Use these as inspiration, then make it your own.
- **Differentiator** — What is the ONE thing someone will remember about this interface?

**CRITICAL**: Choose a clear conceptual direction and execute it fully. Bold maximalism and refined minimalism both work — the key is intentionality, not intensity.

## Aesthetic Rules

**Typography**
- Choose fonts that are beautiful, unique, and unexpected. NEVER use Arial, Inter, Roboto, or system fonts.
- Pair a distinctive display font with a refined body font.

**Color**
- Commit to a cohesive palette via CSS variables.
- Dominant color + sharp accent outperforms timid, evenly-distributed palettes.

**Motion**
- CSS-only for HTML; Motion library for React.
- ONE well-orchestrated page load with staggered reveals (`animation-delay`) creates more delight than scattered micro-interactions.
- Scroll-triggered reveals and hover states should surprise the user.

**Layout**
- Break the grid. Use asymmetry, overlap, diagonal flow, generous negative space, OR controlled density.
- Never default to predictable card/grid patterns.

**Backgrounds & Depth**
- Create atmosphere, not flat colors. Use: gradient meshes, noise textures, geometric patterns, layered transparencies, dramatic shadows, decorative borders, grain overlays, custom cursors.

## NEVER
- Generic font families: Inter, Space Grotesk, Roboto, Arial, system fonts
- Clichéd color schemes (especially purple gradients on white)
- Predictable layouts and cookie-cutter component patterns
- The same aesthetic across generations — vary light/dark, fonts, and style every time

## Implementation

Match code complexity to the aesthetic vision:
- Maximalist → elaborate animations, layered effects, expressive markup
- Minimalist → restraint, precision spacing, careful typographic detail

**Project stack**: React 19 · Inertia 3 · Tailwind 4 · TypeScript 5.7  
Use Tailwind utility classes. Prefer CSS variables for theme tokens. Use Motion for React animations.

---

Claude is capable of extraordinary creative work. Don't hold back — commit fully to a distinctive vision and show what's possible when you think outside the box.
