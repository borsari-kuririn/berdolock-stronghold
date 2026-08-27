# Berdolock's Stronghold — Agent Instructions

## Documentation Convention

All Markdown (`.md`) files created in this project must be saved inside the `documentation/` directory at the root of the repository.

Do **not** create `.md` files at the root or in any other directory unless the file is a project-level meta file (`AGENTS.md`, `README.md`).

### Rule summary

| File type | Correct location |
|-----------|-----------------|
| Rules, lore, tables, guides | `documentation/` |
| Agent instructions | root (`AGENTS.md`) |

---

## Asset Convention

All visual assets (sprites, dungeon views, portraits, UI elements) **must be drawn using CSS and/or JavaScript**.

Do **not** add image files (`.png`, `.jpg`, `.gif`, `.svg`, `.webp`) to the project for game visuals.

| Asset type | Correct implementation |
|------------|------------------------|
| Enemy / NPC sprites | JS canvas pixel arrays (`public/assets/js/sprites.js`) |
| Dungeon corridor view | Pure CSS geometry (nested `div` perspective) |
| UI icons / decorations | CSS only (`::before` / `::after` content or `box-shadow`) |
| External image files | **Forbidden** for game assets |
