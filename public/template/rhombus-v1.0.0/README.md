# Rhombus

A CRM admin dashboard template built with [Hummingbird](https://hbui.dev) and [Tailwind CSS](https://tailwindcss.com). Plain HTML, powered by Vite.

## Features

- **Dashboard page** with stat cards, an email pie chart, monthly income bars, a revenue trend ([ECharts](https://echarts.apache.org)), a top-performance table and an interactive task list.
- **Sign In and Sign Up pages** with their own layout: a password show/hide toggle, social sign-in buttons and a phone field with a country picker.
- **Responsive layout:** the sidebar turns into an offcanvas menu on small screens.
- **Themed the Hummingbird way:** the Rhombus look comes from overriding Hummingbird's theme variables and component variables in one file, so every button, card, table and input picks it up.
- **Plain HTML output:** the built `dist/` folder opens straight from disk and works on any static host.

## Quick start

### Use the ready-made HTML

No install needed. Open `dist/index.html` in your browser, or upload the `dist/` folder to any static host.

### Customize and rebuild

Requires **Node.js 20.19+** and [pnpm](https://pnpm.io).

```bash
pnpm install
pnpm dev          # http://localhost:3000
```

| Command        | What it does                                              |
| -------------- | --------------------------------------------------------- |
| `pnpm dev`     | Start the dev server with live reload                     |
| `pnpm build`   | Build static HTML, CSS and JS into `dist/`                |
| `pnpm preview` | Serve the production build locally                        |
| `pnpm zip`     | Build, then package source + `dist/` into `release/*.zip` |
| `pnpm format`  | Format the JS, CSS, JSON and Markdown files               |

## Folder structure

```
rhombus/
├── index.html                 # Dashboard page
├── signin.html                # Sign In page
├── signup.html                # Sign Up (free trial) page
├── src/
│   ├── partials/              # Layouts (dashboard + auth), sidebar, topbar, cards, form parts
│   │   └── icons/             # Material icons as partials: {{> icons/notifications}}
│   ├── data/site.json         # Brand, user, navigation, stats, table and task data
│   ├── css/style.css          # Tailwind + Hummingbird + the Rhombus theme
│   ├── js/main.js             # Hummingbird JS, task list, password toggle, phone picker
│   └── js/dashboard.js        # Pie and revenue charts
├── public/                    # Copied as-is (logo + favicon, avatars)
├── build/
│   ├── html-kit.js            # Vite plugin: Handlebars partials + site data
│   ├── classic-scripts.js     # Lets dist/ open straight from disk (file://)
│   └── zip.js                 # Packages the release zip (pnpm zip)
└── vite.config.js
```

Pages are plain HTML with a few [Handlebars](https://handlebarsjs.com) tags, rendered at build time. To add a page, create an `.html` file in the project root using the layout:

```hbs
{{#> layout page="companies" title="Companies"}}
  <h2 class="mb-6 text-4xl font-bold">Companies</h2>
{{/layout}}
```

Then link it in `nav` in `src/data/site.json` with `"page": "companies"` and `"href": "companies.html"`.

## Customizing

All styling lives in `src/css/style.css`, in two layers:

1. **Theme variables** in `@theme`: brand colors (`--color-primary`, `--color-secondary`, `--color-info`, `--color-warning`), surfaces (`--background-color-*`), text (`--text-color-*`), borders, the font and the shared input/button scale (`--input-btn-*`). Change a value here and it updates everywhere.
2. **Component variables**: one block per component (`.btn`, `.card`, `.table`, `.progress`, `.list-group`, ...) sets Hummingbird's `--btn-*`, `--card-*`, `--table-*` variables. For example, every button is 128 × 40 because of `.btn { --btn-height: 2.5rem; min-width: 8rem; }`.

See [Theming](https://hbui.dev/docs/customize/theming) and [Component customization](https://hbui.dev/docs/customize/component).

- **Font:** change `--font-sans` in `src/css/style.css` and the Google Fonts link in `src/partials/head.hbs`.
- **Icons:** add any [Material icon](https://fonts.google.com/icons) SVG to `src/partials/icons/` (with `fill="currentColor"`) and use it by file name.

## Credits

- [Hummingbird](https://hbui.dev), [Tailwind CSS](https://tailwindcss.com), [Vite](https://vite.dev), [Handlebars](https://handlebarsjs.com)
- [ECharts](https://echarts.apache.org) (Apache 2.0), [Material Design Icons](https://github.com/google/material-design-icons) (Apache 2.0), [Pictogrammers MDI](https://pictogrammers.com) briefcase-check icon (Apache 2.0)
- Avatar photos from [Unsplash](https://unsplash.com) (Unsplash License)
- Crafted by [ThemeWagon](https://themewagon.com)

## License

Released under the [MIT License](LICENSE).
