# Cursor Ready-to-Import Rules (Laravel Blade + jQuery + Livewire + Vite + Bootstrap)

This package contains **flexible** agent rules and prompt templates tailored to a traditional server‑rendered Laravel app that uses:
- **Blade** templates (resources/views), multi-page (non‑SPA)
- **jQuery 3.6**, **Axios** for HTTP
- **Livewire** for reactive components
- **Bootstrap 5.1.3** for UI
- **Toastr**, **SweetAlert2** for notifications/modals
- **Slick** and **Owl Carousel** for sliders
- **imagesLoaded** utility
- **Vite** build with Laravel Vite Plugin
- **SQL via Eloquent/Query Builder**

> **Mode**: Flexible — the agent may propose alternatives **only if it explains trade‑offs** and requests approval before adding new libs or changing core tech.

## Install

1. Drop the `.cursor` folder at the root of your Laravel project (next to `app/`, `resources/`, `routes/`, etc.).
2. Optionally keep `/prompts` anywhere you like; they’re just templates.
3. In Cursor, open your project. The rules should auto‑apply (they’re plain text “guardrails” for your prompts).

## How to Use

- **Always reference** the rules in your prompts (Cursor remembers context better when you explicitly mention them).  
  Example:  
  > “Follow the rules in `.cursor/rules/*.mdc`. Only modify the minimal relevant files. Use Blade + jQuery + Livewire patterns.”

- Use the prompt templates in `/prompts`:
  - `feature_prompt.txt`
  - `bugfix_prompt.txt`
  - `refactor_prompt.txt`
  - `review_prompt.txt`

- Keep commits small. After each change, ask the agent to **self‑review and refactor** within these rules.

## Structure

```
.cursor/
  rules/
    global_rules.mdc
    php_laravel_rules.mdc
    blade_frontend_rules.mdc
    js_rules.mdc
    sql_rules.mdc
    css_rules.mdc
    security_rules.mdc
    vite_rules.mdc
    livewire_rules.mdc
    ui_libs_rules.mdc
prompts/
  feature_prompt.txt
  bugfix_prompt.txt
  refactor_prompt.txt
  review_prompt.txt
```

## Notes

- Files are intentionally concise; expand them as your conventions evolve.
- If you later migrate from jQuery to Alpine/Vue/React, create a new rules file and switch references in prompts.
