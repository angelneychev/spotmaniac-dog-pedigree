=== Spotmaniac Dog Pedigree ===
Contributors: angelneychev
Tags: dogs, dog catalog, breeder, kennel, dog registry
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn your WordPress site into a professional dog registry. Manage your kennel's dogs with detailed records, public catalog, photos, and clean single-dog pages - all without writing a line of code.

== Description ==

**Built for breeders, kennel clubs, and dog registries** who are tired of managing pedigree data in scattered Excel files, Facebook albums, or expensive proprietary software.

Spotmaniac Dog Pedigree turns your WordPress site into a structured, searchable, public-facing catalog of your dogs. Each dog gets a dedicated page with photo, registration data, parents, and full breeding info. Visitors browse a responsive catalog with filters. You manage everything from the familiar WordPress admin.

**No page creation. No theme conflicts. No technical setup.** Activate the plugin, save permalinks once, and your catalog is live at `/dogs/`.

= 🐶 Why Spotmaniac Dog Pedigree? =

* **Designed for dogs, not generic CPTs.** 22 purpose-built fields cover every detail a breeder needs - name, sex, color, dates, registration numbers, microchip, tattoo, club number, breeder, owner, titles, health, description, and more.
* **Looks great out of the box.** Responsive card grid catalog, clean single-dog page with photo hero, color-coded badges, and a Litter Mates section that auto-builds from parent links.
* **Owner self-service.** Logged-in owners can update their own dogs' photos, titles, and basic info via a secure REST endpoint - no admin access needed.
* **Theme-agnostic.** Works with any well-built theme. Override any template by copying it to your theme folder.
* **Privacy-respecting.** No external tracking, no telemetry, no calls home. Your data stays in your database.

= 📋 22 Fields per Dog =

* Registered Name & Call Name
* Sex (Male / Female)
* Color, Size
* Breeding Status (Available, Active stud, Active brood, Retired, Sold, etc. - fully customizable)
* Birth Date, Death Date
* Registration Number, Registration Date
* Tattoo Number, Microchip, Club Number
* Breeder, Owner (with optional link to a WordPress user)
* Titles (free text - IPO, KKL, V-rated, BH, etc.)
* Health (free text - HD, ED, DM, OFA, certifications)
* Description
* Photo (via WordPress Media Library)
* Father (Sire) - autocomplete search across all dogs
* Mother (Dam) - autocomplete search across all dogs
* Plus unlimited **custom dropdown fields** you define yourself

= 🌐 Public Catalog =

* Responsive card grid at `/dogs/` (URL prefix configurable)
* Search by name
* Filters by sex
* Customizable filters by color, breeding status (define your own dropdown values)
* Built-in pagination
* Works automatically - no page creation needed
* Embed anywhere with `[dogped-catalog]` shortcode
* Highlight specific dogs with `[dogped-featured count="6"]`

= 🎴 Single Dog Pages =

* Photo + key info hero layout
* Color-coded badges for sex (♂ blue, ♀ pink) and status
* Sections: Basic Info, Identification, Health, Description, Litter Mates
* **Drag-and-drop section reordering** from settings
* Toggle visibility of any field
* Fully translatable

= ⚙️ Settings =

* Visual repeater UI - add, remove, reorder dropdown options
* Define custom dropdown fields beyond the built-in ones
* Set URL prefix (e.g., `/dogs/` → `/kennels/` → `/registry/`)
* Items per page, default sort order
* Photo size limit
* Toggle which fields show on single dog pages
* Reorder sections by drag-and-drop

= 🔧 Developer Friendly =

* **Template override** - copy any template to `your-theme/spotmaniac-dog-pedigree/` and customize
* Clean CSS classes on every element (`.dogped-*` namespace)
* **REST API** at `/wp-json/spotmaniac-dog-pedigree/v1/` - full CRUD + parent search
* Action and filter hooks throughout
* PSR-style code structure
* No JavaScript framework dependencies - vanilla JS, jQuery only where needed
* Translation-ready (POT file included)
* Plugin Check passing
* Clean uninstall (removes all data when deleted, leaves no orphan tables / options)

= 🎯 Use Cases =

* **Breeders** - public catalog of breeding stock + retired + puppies
* **Kennel clubs** - central registry for member dogs
* **Sire/dam directories** - searchable database for stud services
* **Show dog portfolios** - track titles, achievements, health
* **Multi-breed registries** - combined with the Pro add-on, manage hierarchical breed taxonomies

= 💎 Pro Add-on =

If you need more powerful features for active breeding operations, the **Spotmaniac Dog Pedigree Pro** add-on extends the free plugin with:

* **3-5 generation pedigree tree** on every dog page (color-coded, configurable depth)
* **Hierarchical breed taxonomy** - breeds + varieties for multi-breed registries
* **CSV import / export** - bulk import an entire pedigree in one upload
* **Structured health tests** (HD, ED, DNA, Eye, Heart, custom) with date, lab, certificate fields
* **Frontend Breeder Dashboard** - your puppy buyers manage their own dogs
* **Frontend submit form** - breeders add dogs without admin access
* **Schema.org SEO** - Animal JSON-LD on every dog page (Google understands your data)
* **Email notifications** - configurable HTML templates for approvals
* **Automatic plugin updates** while subscription is active

Pro is sold as a separate plugin at <https://dogspedigree.lemonsqueezy.com/> with a 14-day free trial.

**The free plugin works fully on its own** - Pro is for those who want more.

== Installation ==

= Quick Start =

1. Install and activate **Spotmaniac Dog Pedigree** from Plugins → Add New.
2. Go to **Settings → Permalinks** and click Save Changes.
3. Your catalog is live at `/dogs/` (or whatever URL prefix you set).
4. Go to **Dogs → Add New Dog** to add your first dog.
5. (Optional) Go to **Dogs → Settings** to customize colors, breeding statuses, and add custom fields.

= Manual Install =

1. Upload the plugin folder to `/wp-content/plugins/spotmaniac-dog-pedigree/`.
2. Activate through the Plugins screen.
3. Save permalinks (step 2 above).

== Frequently Asked Questions ==

= Do I need to create pages? =

No. The `/dogs/` archive and individual dog pages are created automatically by the plugin. Just save permalinks once after activation.

= Can I change the URL prefix? =

Yes. Go to **Dogs → Settings → URL Prefix**. After changing, go to **Settings → Permalinks** and click Save to refresh the rewrite rules.

= Does it work with my theme? =

Yes. The plugin uses minimal, scoped CSS and inherits your theme's typography, form styles, and overall design language. We've tested with Twenty Twenty-Five, Twenty Twenty-Four, Astra, Kadence, GeneratePress, Hello Elementor, and the OceanWP framework. If you want full control, override any template from your theme folder.

= How do I add dropdown options like colors? =

Go to **Dogs → Settings → Dropdown Field Options**. Use the + Add button to define options for Color, Size, and Breeding Status. You can also create custom dropdown fields beyond the built-in ones - perfect for breed-specific traits or club-specific data.

= Can I edit dogs from the front end? =

Yes - the REST API supports an owner-update endpoint that lets a logged-in owner edit a limited set of fields (photo, titles, color, call name, description) on dogs they own. The Pro add-on adds a full frontend submit + edit form for breeders.

= Can I import existing data? =

The free plugin supports manual entry. The Pro add-on adds CSV import that auto-creates parent stubs by name, so you can import an entire kennel's pedigree in one upload.

= Is the plugin GDPR-friendly? =

Yes. The plugin doesn't collect or transmit any personal data. It stores only the data you enter into your own database. No external services, no telemetry, no cookies set by the plugin itself.

= Does it support multilingual sites? =

Yes. The plugin is fully translation-ready (POT file included). All admin and frontend strings can be translated. You can pair it with WPML, Polylang, or TranslatePress.

= Can I show only specific dogs on a page? =

Use `[dogped-catalog]` for the full filterable catalog or `[dogped-featured count="6"]` for a featured grid. Both shortcodes accept attributes for fine-tuning.

= How do I uninstall cleanly? =

Deactivating the plugin leaves all data intact (so you can reactivate without losing anything). Deleting the plugin via the Plugins screen removes all dog posts, meta, options, and transients - clean uninstall, no orphan data.

= Where can I get support? =

Use the **Support** tab on this page (community forum) for general questions. Pro customers receive priority email support.

== Screenshots ==

1. Public dog catalog with responsive card grid, search, and filters
2. Single dog page with photo hero, color-coded badges, and full information layout
3. Admin - Add New Dog with all 22 fields organized into sections
4. Admin - Settings page with the visual repeater for dropdown options
5. Admin - Dog list with custom columns (photo, sex, color, birth date, registration)

== Changelog ==

= 1.0.4 =
* Tested with WordPress 7.1.

= 1.0.3 =
* Fixed: a value that is not a full date is no longer guessed at. A bare year such as 2015 was being turned into today's date, "0000-00-00" into a date before the calendar begins, and a month and year into the first of that month. An invented date on a pedigree looks correct while being wrong, so such a value is now refused and reported instead.
* Fixed: the dog catalogue archive ignored the page number in the address, so every page after the first showed the first page again.
* Fixed: search terms from the address bar were sanitised without being unslashed first, which could leave stray backslashes in a search for a name containing an apostrophe.

= 1.0.2 =
* Fixed: selecting a Father or Mother from the search results sometimes did nothing, leaving the field on "- None -" with the typed text still in the box. The result list could be dismissed before the click was registered; picking a result is now handled the moment the mouse goes down, so the selection always sticks.
* Fixed: picking a parent from the search box emptied the dropdown of its other entries.
* New: the Father and Mother dropdowns are now filled with the 50 most recently added dogs of the matching sex, so a parent can be chosen without typing anything. The search box below each dropdown still reaches every other dog in the catalogue.
* New: a dog is no longer offered as its own parent, neither in the dropdown nor in the search results, and a submission that still carries its own ID is now rejected on save.
* New: a parent link that would close a loop in the pedigree is now refused. If the dog being edited already appears somewhere in the ancestry of the dog picked as its parent, the save is rejected with an explanation, so two dogs can never end up descending from each other.
* Fixed: when a save was rejected by validation, the edit screen kept the previous values without saying why. The reasons are now shown as an error notice at the top of the screen.
* Changed: an already assigned parent is always kept in the dropdown even when it falls outside the 50 listed dogs, so saving can never silently drop it.
* Removed: double-clicking the parent search box no longer clears the selection. Clearing is done by choosing "- None -" in the dropdown.

= 1.0.1 =
* Confirmed compatibility with WordPress 7.0.
* Text and typography consistency improvements across admin labels, dropdown options, and the help page.

= 1.0.0 =
* Initial release.
* Dog custom post type with 22 fields covering everything a breeder needs.
* Public catalog with search and filter capabilities.
* Single dog page with configurable layout and section visibility.
* Settings page with visual repeater UI for dropdown options.
* Custom dropdown fields support.
* Template override system from theme folder.
* REST API at `spotmaniac-dog-pedigree/v1` with full CRUD + parent search.
* Owner self-edit endpoint with limited field set.
* Translation-ready (POT file included).
* Clean uninstall - no orphan data left behind.

== Upgrade Notice ==

= 1.0.1 =
Compatibility with WordPress 7.0 and minor text consistency improvements.

= 1.0.0 =
Initial release of Spotmaniac Dog Pedigree. Welcome!
