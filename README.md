# Craft starter

Makes a new Craft CMS site on DDEV, set up like jonleverrier.local: Vite + SCSS in
`build/`, Craft in `craft/`, docroot `public/`, a `frontend` module, critical CSS,
AVIF transforms and an SVG sprite map.

## Install Copier (once)

```bash
python3 -m pip install --user copier
```

(`brew install copier` works too, if Homebrew is installed.) `bin/new` finds the pip
install itself; add `export PATH="$HOME/Library/Python/3.9/bin:$PATH"` to `~/.zshrc`
only if you want to run `copier` directly.

## Make a site

From this folder:

```bash
cd ~/Sites/starter
bin/new myidea.local
```

The site is created **beside the starter**, in a folder named after the domain:
`~/Sites/myidea.local`. Run `bin/new` with no domain and it asks for one. It stops if
that folder already exists.

It then asks for the site name, the admin email and username, and the admin password.
It starts DDEV, installs Craft and the plugins, builds the assets, makes the first
commit and opens the site. The control panel is at `/manager`. Each new site uses the
starter's latest commit.

DDEV asks for your Mac password once, to add the `.local` domain to `/etc/hosts`.

If a step fails, the folder is kept. Fix the problem and resume from that step:

```bash
cd ~/Sites/myidea.local
bash tools/setup.sh --from=<step>
```

To create a site somewhere else: `SITES_DIR=~/Projects bin/new myidea.local`, or call
Copier directly: `copier copy --trust ~/Sites/starter <any/path>`.

## Craft 6 (beta)

Craft 6 lives on the `craft6` branch; `main` stays on Craft 5. To make a Craft 6 site:

```bash
cd ~/Sites/starter
bin/new --craft6 myidea.local
```

It works from a `main` checkout: `--craft6` builds from the `craft6` branch's latest
commit. Everything else (questions, setup steps, `--from`) is the same. Update a Craft 6
site from its own branch, or `copier update` moves it back to Craft 5:

```bash
copier update --trust --vcs-ref=craft6
```

What's different on Craft 6 (6.0.0-beta.1), and why:

| | Craft 5 (`main`) | Craft 6 (`craft6`) |
|-|-|-|
| PHP | 8.4 | 8.5 (Craft 6 requires it) |
| `craft/` | Craft's own layout | a Laravel app: `bootstrap/`, `config/` (Laravel) and `config/craft/` (Craft), `resources/views/` (templates), `app/` |
| Web root | `public/` | `public/`, unchanged (`usePublicPath` in `craft/bootstrap/app.php`) |
| DDEV | type `craftcms` | type `laravel`; `ddev craft …` still works (`.ddev/commands/web/craft`), as does `ddev artisan …` |
| `.env` | `CRAFT_DB_*`, `CRAFT_SECURITY_KEY` | Laravel's `DB_*`, `APP_KEY`, `APP_ENV`, `APP_DEBUG` |
| Vite | `nystudio107/craft-vite` | Laravel's Vite (`laravel-vite-plugin`, Craft's `vite()` Twig function), plus `craft.frontend` helpers for inlined fonts, critical CSS and the async stylesheet. No Craft 6 version of craft-vite exists |
| Email | `craftcms/postmark` plugin | Laravel's Postmark mailer: `MAIL_MAILER=postmark` and `POSTMARK_API_KEY` in production; Mailpit locally |
| Expanded Singles | installed | left out: no Craft 6 version yet |
| Error pages | `_views/<code>` (`errorTemplatePrefix`) | Craft 6 looks in `errors/`; `errors/error.twig` includes `_views/error` |
| `general.php` | `GeneralConfig::create()->…` | an array: Laravel loads it before Craft boots, and some setters need Craft running |
| `defaultCpLanguage` | `en-GB` | left out: on beta.1 it loops until PHP runs out of memory. Add it back when fixed |

| `frontend` module | a Yii module, set up in `config/app.php` | a Laravel service provider (`modules/frontend/FrontEndServiceProvider.php`, listed in `bootstrap/providers.php`); no `config/craft/app.php` |
| Setup commands | `frontend/setup/home`, `frontend/setup/assets` | artisan commands: `frontend:setup:home`, `frontend:setup:assets` |
| Asset storage | a Local filesystem in project config | the `assets` disk in `config/filesystems.php` (`public/assets`, served at `/assets`); the Local volume points at it |

Nothing in the starter's own code goes through `craftcms/yii2-adapter`. It's still
installed for its Yii2 Loose Ends utility (Utilities in the control panel), which lists
anything a site adds that does. Remove it from `composer.json` once a site no longer
needs that.

To bring a Craft 6 change into the branch, or a fix from `main` into it: `git switch craft6`,
merge or edit, `bin/smoke` (it builds whatever branch is checked out), commit.

## Remove a site

```bash
cd ~/Sites/starter
bin/remove myidea.local
```

Deletes the DDEV project (containers, database and the `/etc/hosts` entry, which can ask
for your Mac password) and the folder `~/Sites/myidea.local`. It asks you to type the
domain again first, and only removes folders made from this starter. There's no undo.

## Bump a version

Every version pin is in `copier.yml` (PHP, MySQL, Node, Composer, Craft and plugins).
npm packages are ranges in `template/package.json.jinja`.

1. Edit the pin.
2. `bin/smoke` (makes a real site at `starter-smoke.ddev.site`, checks it, deletes it).
3. Commit and tag: `git tag v1.x.y`.

`bin/smoke --render-only` is the quick check while editing the template.

## Update an existing site

In the site, with a clean git tree:

```bash
copier update --trust
```

Review the changes, resolve any conflicts, commit. Then, depending on what changed:

| Changed               | Run                    |
|-----------------------|------------------------|
| `.ddev/`              | `ddev restart`         |
| `craft/composer.json` | `ddev composer update` |
| `package.json`        | `ddev npm install`     |

These belong to the site from day one and are never touched by an update:
`craft/config/project/**`, `craft/templates/_views/single/home/default.twig`,
`build/scss/theme/_setup.scss`.

## What's in a site

| Path | What |
|------|------|
| `.ddev/` | DDEV config, Vite port, AVIF and Chromium images |
| `build/` | SCSS, JS, icons (sprite map), fonts, static files |
| `craft/config/` | general, app, vite, routes |
| `craft/modules/frontend/` | `craft.frontend` helpers, AVIF options, setup commands |
| `craft/templates/` | `_layouts`, `_includes`, `_routers`, `_views`, `_components`, `_macros` |
| `tools/setup.sh` | the one-off setup |
| `tools/sync/` | pull/push the database and assets (fill in `PRODUCTION_SSH_*` in `craft/.env`) |
