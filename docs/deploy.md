# Deploying to the live server

The **Build and deploy** workflow (`.github/workflows/deploy.yml`) builds the site
from `src/` with Eleventy, then uploads the built pages to your real server by
FTP/FTPS and publishes the same pages to the GitHub Pages preview.

- **Automatic:** it runs when a pull request is merged into `main`.
- **Manual:** open the repository's **Actions** tab, choose **Build and deploy**,
  press **Run workflow**. A manual run is a *dry run* by default (it builds the site
  and lists what would change on the server, but publishes nothing). Untick
  **Dry run** to publish for real.

GitHub Pages must be set to publish from **GitHub Actions** (repository
**Settings, Pages, Build and deployment, Source**). This was set up once already.

So the flow is: change on a branch, review the pull request, merge it, and the
merge publishes to the server. Closing a pull request without merging changes nothing.

Until the settings below are added, merging is harmless: the workflow notices the
settings are missing, uploads nothing and says so in its log.

## One-time setup

You add these yourself in GitHub, so the password never passes through anyone else.
Go to the repository, then **Settings, Secrets and variables, Actions**.

### Secrets (tab "Secrets", New repository secret)

| Name | Value |
| --- | --- |
| `FTP_SERVER` | The FTP host name from your hosting control panel, for example `ftp.example.co.uk` |
| `FTP_USERNAME` | The FTP username |
| `FTP_PASSWORD` | The FTP password |

Create a dedicated FTP account for this if your host allows it (in cPanel: **FTP
Accounts**), limited to the website folder, rather than using your main login.

### Variables (tab "Variables", New repository variable) - optional

| Name | Default | Meaning |
| --- | --- | --- |
| `FTP_SERVER_DIR` | `./` | Folder on the server to upload into, for example `public_html/`. Must end with `/`. If the FTP account already lands in the website folder, leave it unset. |
| `FTP_PROTOCOL` | `ftps` | `ftps` (encrypted, recommended), or `ftp` if your host does not support encryption. |

## First deployment: do a dry run

1. Add the secrets (and variables if needed).
2. **Actions, Build and deploy, Run workflow**, leave **Dry run** ticked.
3. Open the run and read the log. It should list the site files it would upload.
4. If that looks right, run it again with **Dry run** unticked.
5. Open https://coastlinesafetyservices.co.uk/ and check the new site shows.

## Replacing the Coming Soon WordPress page

The workflow only adds and updates the website files. It does **not** delete WordPress
or anything else already on the server. The new `index.html` normally takes priority
over WordPress's `index.php`, so the new site shows. WordPress stays on the server
underneath. If the old page still appears after deploying, the server is preferring
`index.php`; ask your host to put `index.html` first in `DirectoryIndex`, or tell
Claude and the workflow can be adjusted.

## What gets uploaded

Only the built site (the `_site/` folder the build creates): the four pages,
`styles.css`, `menu.js`, `form.js`, `contact.php` and the `images/` folder. Source
files such as the layout, `site.json`, `README.md` and `docs/` are never uploaded.
The workflow also stores a small file `.ftp-deploy-sync-state.json` on the server so
it can upload only what changed next time. Leave it there.

## HTTPS redirect and HSTS (`.htaccess`)

Each deploy also manages the server's `.htaccess` file, which:

- redirects every `http://` address to the same `https://` address (permanent, 301)
- sends a `Strict-Transport-Security` (HSTS) header, so browsers always use https
  for this site for a year (subdomains are not included)

It does not replace the server's file. It downloads the existing `.htaccess`, puts
these rules in a section marked `# BEGIN Coastline Safety Services` ...
`# END Coastline Safety Services` at the top, keeps everything else unchanged, and
uploads the result. Anything you add to `.htaccess` yourself (for example through
the hosting control panel) survives, as long as it is outside that section. If the
existing file cannot be read, the deploy stops and uploads nothing.

To read the existing file securely, the deploy checks the FTP server's certificate.
GoDaddy's server uses the certificate for `*.prod.sxb1.secureserver.net`, which is
the default. If the host ever moves the site and the deploy fails with a certificate
error (`curl: (60)`), add a repository variable `FTP_TLS_NAME` with the name on the
new certificate.

## If something goes wrong

- **Login failed:** check the secrets, and whether your host wants `ftp` rather than `ftps`.
- **Files land in the wrong folder:** set `FTP_SERVER_DIR`.
- **Need to undo a change:** revert the pull request on GitHub (it creates a new pull
  request); merging that redeploys the previous version.
