=== TP Skew Slider ===
Contributors: youragency
Tags: elementor, slider, portfolio, fullscreen, scroll
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later

Full-viewport skew slider for Elementor. Each slide is an empty shell
that accepts a custom Elementor template or pasted HTML/shortcode.

== Description ==

Butter-smooth full-viewport scroll slider, ported from the Agntix theme's
portfolio creative skew slider.

Features:
* Each slide is a blank canvas — choose a background (image or colour)
  and then inject content via:
    - A saved Elementor Template (Section / Container)
    - Custom HTML / shortcode
    - The built-in text overlay (label + big title with link)
* Scroll-hijack mode — slider sticks to the viewport, each scroll tick
  advances one slide, page continues scrolling after the final slide and
  re-engages when scrolled back.
* Free-flow mode — slider scrolls normally with the page.
* Prev/Next buttons, mouse wheel, touch swipe all supported.
* Fully responsive with sensible breakpoints.
* No extra dependencies beyond GSAP (already loaded by Elementor / theme).

== Installation ==

1. Upload the `tp-skew-slider` folder to `/wp-content/plugins/`.
2. Activate via Plugins > Installed Plugins.
3. Elementor must be installed and active.

IMPORTANT — GSAP requirement:
The plugin's animations rely on GSAP (gsap, Observer plugin).
GSAP is already bundled in many premium Elementor themes (Agntix, etc.).
If your theme does NOT include GSAP you must enqueue it yourself or install
the free "GSAP for Elementor" helper plugin.

== Usage ==

1. Open any page/post in the Elementor editor.
2. Search for "TP Skew Slider" in the widget panel.
3. Drag it onto the canvas.
4. Under "Slides" add as many slides as you like. For each slide:
   a. Set a Background Image (or Solid Color, or None).
   b. Set an optional Image Overlay colour/opacity.
   c. Under "Slide Content Source" choose:
      * "Elementor Template" — pick any saved Section/Container from the dropdown.
      * "Custom HTML / Shortcode" — paste raw HTML or a shortcode.
      * "None" — leave the slide empty (useful when the background image IS the content).
   d. Optionally enable the Text Overlay to show a label + big heading on top.
5. Under "Scroll Behaviour" choose Hijack or Free-flow.
6. Style the title typography and nav buttons in the Style tab.

== Elementor Template Tip ==

Create your real estate listing layout (or any content) as a saved Section /
Container in Elementor > Templates > Saved Templates. Then select it in the
"Choose Template" dropdown for each slide. The template is rendered live inside
the slide shell with full Elementor CSS and JS intact.

== Changelog ==

= 1.0.0 =
* Initial release.
