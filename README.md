# Coastline Safety Services website

Static HTML and CSS site for Coastline Safety Services (thermal imaging and
lightning conductor testing). Published with GitHub Pages from the `main` branch.

Live site: https://brassmonkeyman.github.io/hargreaves-coastline/

## How changes are made

Nothing goes straight to `main`. Every change follows the same steps:

1. The change is made on its own branch (for example `add-van-photo`).
2. The branch is pushed and a **pull request** is opened against `main`.
3. You review the pull request on GitHub: read the summary, look at the changed
   files and tick the checklist in the description.
4. You press **Merge** when you are happy. GitHub Pages then publishes the change
   to the live site within a minute or two.

Until a pull request is merged, the live site does not change. To reject a change,
close the pull request without merging.

## Site files

| File | What it is |
| --- | --- |
| `index.html` | Home page |
| `thermal-imaging.html` | Thermal imaging page |
| `lightning-conductor-testing.html` | Lightning conductor testing page |
| `about-contact.html` | About and contact page |
| `styles.css` | All styling |
| `menu.js` | Phone menu (hamburger button) |
| `images/` | Logo and photos |

## Still to finish

- Accreditations and qualifications (hidden placeholder notes are in the pages;
  see `.placeholder` in `styles.css`)
- Contact form is not connected to anything yet
- Confirm the BS EN 62305 wording on the lightning page
