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
