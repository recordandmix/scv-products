=== SCV Products ===
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPL-2.0-or-later

Custom Product catalog, Categories and Collections taxonomies, and Always Available product cards block for SoCal VoCals Swag.

== Changelog ==

= 1.1.1 =
* Sold out and Coming soon colors keep a greyed, non-clickable pill in the Shop button's place (SOLD OUT / COMING SOON), including on cards where every color is unavailable. Switching colors no longer shifts the card layout.

= 1.1.0 =
* Adds generic Categories taxonomy (`scv_category`) with display order (`_scv_category_order`), rendering per-category sections with scoped collection filter pills.
* Moves the selected color label (`Color: ...`) onto its own line above wrapping swatch dots.

= 1.0.9 =
* Updater test release

= 1.0.8 =
* Adds a GitHub-backed update checker. After this version is installed, new releases appear under Dashboard > Updates. Click Check again, then Update, or turn on auto-updates for the plugin.
* No change to product cards, swatches, or shop links.

= 1.0.7 =
* Out of stock and Coming soon colors stay visible on the card. Swatches are dimmed with a diagonal slash, remain clickable to show that color's photo, and are labeled for assistive tech. The shop button is replaced by non-link "Sold out" or "Coming soon" text, and never links to the shop for a non-live color.
* The default selected color is the first live color, when one exists.
* A card whose colors are all non-live stays on the page with a small Sold out badge (or Coming soon, if every color is coming soon) and no shop link.
* Single-color products still have no swatch row or "Color:" line. If that one color is non-live, the badge and no-button treatment still apply.
* Optional per-color provider: the shop button reads "Shop on [Provider]", using the color's provider, then the product provider, then Zazzle.

= 1.0.6 =
* Single-color products no longer show a color picker, a color-options heading, or a "Color:" line. The photo, lightbox, and shop link still use that color's data.
* Cards with two or more colors are unchanged.
