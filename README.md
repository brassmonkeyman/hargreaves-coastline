# Coastline Safety Services website

Website for Coastline Safety Services (thermal imaging and lightning conductor
testing). The pages are plain HTML built with [Eleventy](https://www.11ty.dev/),
so the header and footer are written once and shared by every page.

- Live site: https://coastlinesafetyservices.co.uk/
- Preview copy (GitHub Pages): https://brassmonkeyman.github.io/hargreaves-coastline/

## How changes are made

Nothing goes straight to `main`. Every change follows the same steps:

1. The change is made on its own branch (for example `add-van-photo`).
2. The branch is pushed and a **pull request** is opened against `main`.
3. You review the pull request on GitHub: read the summary, look at the changed
   files and tick the checklist in the description.
4. You press **Merge** when you are happy. The **Build and deploy** workflow then
   builds the site and publishes it to the live server and to GitHub Pages within
   a minute or two.

Until a pull request is merged, the live site does not change. To reject a change,
close the pull request without merging.

## Where things are

Everything that makes up the site is in `src/`. The built site goes to `_site/`
(created by the build, never edited by hand or stored in the repository).

| File | What it is |
| --- | --- |
| `src/_includes/layouts/base.njk` | **Shared layout**: page head, header and menu, footer, scripts |
| `src/_data/site.json` | **Shared details**: company name, phone, email, address, menu items, accreditation logos |
| `src/index.html` | Home page content |
| `src/thermal-imaging.html` | Thermal imaging page content |
| `src/lightning-conductor-testing.html` | Lightning conductor testing page content |
| `src/about-contact.html` | About and contact page content |
| `src/styles.css` | All styling |
| `src/menu.js` | Phone menu (hamburger button) |
| `src/form.js` | Contact form: sends without leaving the page and shows messages |
| `src/contact.php` | Contact form handler: emails enquiries (needs PHP on the server) |
| `src/images/` | Logo, photos and accreditation logos |
| `eleventy.config.js` | Build settings |

### Common edits

- **Phone, email, address or footer text:** edit `src/_data/site.json`. It updates
  every page.
- **Add or remove an accreditation logo:** put the image in
  `src/images/accreditations/` and add or remove its line in `src/_data/site.json`.
- **Change a menu item:** edit `nav` in `src/_data/site.json`.
- **Change the header or footer layout:** edit `src/_includes/layouts/base.njk`.
- **Change a page's text:** edit that page in `src/`. The lines between the two
  `---` at the top set the page title, the search-engine description, which menu
  item is highlighted (`nav`), and any extra scripts.

### Adding a new page

Create `src/new-page.html` starting with:

```
---
title: "Page Title | Coastline Safety Services"
description: "One sentence for search engines."
nav: none
---
<div class="page-hero">...</div>
```

It is published as `new-page.html` with the shared header and footer. To list it
in the menu, add it to `nav` in `src/_data/site.json` and use the same `key` as
`nav:` in the page.

## Previewing on your computer (optional)

Needs [Node.js](https://nodejs.org/) installed. In this folder run:

```
npm install
npm start
```

Then open the address it prints (usually http://localhost:8080). Pages rebuild as
you save. `npm run build` builds once into `_site/` without starting a preview.

## Contact form

The form on the About & Contact page posts to `contact.php`, which emails the
enquiry to `info@coastlinesafetyservices.co.uk` with the visitor's address as
Reply-To, so you can answer by replying. Nothing is stored. To change where
enquiries go, edit the settings at the top of `src/contact.php`.

Spam protection: a hidden "trap" field, a minimum time on the page, and a limit
of five messages per hour from one connection.

The form only works on the real server, because GitHub Pages cannot run PHP. On
the GitHub Pages copy it shows a message asking visitors to call or email instead.

## Still to finish

- Accreditations and qualifications text (hidden placeholder notes are in the
  pages; see `.placeholder` in `src/styles.css`)
- Confirm the BS EN 62305 wording on the lightning page
