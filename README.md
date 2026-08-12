# Spotmaniac Dog Pedigree

[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/spotmaniac-dog-pedigree)](https://wordpress.org/plugins/spotmaniac-dog-pedigree/)
[![Downloads](https://img.shields.io/wordpress/plugin/dt/spotmaniac-dog-pedigree)](https://wordpress.org/plugins/spotmaniac-dog-pedigree/)
[![Tested up to](https://img.shields.io/wordpress/plugin/tested/spotmaniac-dog-pedigree)](https://wordpress.org/plugins/spotmaniac-dog-pedigree/)
[![License: GPL v2 or later](https://img.shields.io/badge/license-GPLv2%2B-blue)](https://www.gnu.org/licenses/gpl-2.0.html)

![Spotmaniac Dog Pedigree](https://ps.w.org/spotmaniac-dog-pedigree/assets/banner-1544x500.png)

Turn a WordPress site into a dog registry. Kennels, breed clubs, and breeders
use it to keep structured records for each dog, link parents into a pedigree,
and publish a searchable public catalogue.

This repository holds the source of the free plugin published on
[WordPress.org](https://wordpress.org/plugins/spotmaniac-dog-pedigree/). The
paid Pro add-on is developed separately and is described at the bottom of this
document.

## Contents

- [What the plugin does](#what-the-plugin-does)
- [Screenshots](#screenshots)
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting started](#getting-started)
- [What is stored for each dog](#what-is-stored-for-each-dog)
- [Publishing dogs on the site](#publishing-dogs-on-the-site)
- [Settings](#settings)
- [REST API](#rest-api)
- [Hooks](#hooks)
- [Uninstalling](#uninstalling)
- [Repository layout](#repository-layout)
- [Releasing](#releasing)
- [Pro add-on](#pro-add-on)
- [License](#license)

## What the plugin does

Dogs are stored as a custom post type, `dogped_dog`, with their own admin
screens. Each dog carries 22 structured fields covering identity, registration,
health, and ownership, rather than being written as free text into the post
body.

Parents are real links between records, not names typed twice. The Father and
Mother pickers list recent dogs of the matching sex and search the whole
catalogue, and the plugin refuses links that would break the pedigree: a dog
cannot be its own parent, and a link that would make two dogs descend from each
other is rejected on save with the reason shown on screen.

The public side ships an archive, a single-dog page, and two shortcodes, all of
which a theme can override.

## Screenshots

The images below are the ones published on WordPress.org, so they always show
the current release.

**Public catalogue with a responsive card grid, search, and filters**

![Public dog catalogue listing dogs as cards, with a search box and filter controls above the grid](https://ps.w.org/spotmaniac-dog-pedigree/assets/screenshot-1.png)

**Single dog page with a photo hero, colour-coded badges, and the full record**

![Single dog page showing the dog photo, badges for sex and status, and the information sections below](https://ps.w.org/spotmaniac-dog-pedigree/assets/screenshot-2.png)

**Add New Dog, with all 22 fields grouped into sections**

![Admin screen for adding a dog, with the fields grouped into Dog Details, Parents, and Owner boxes](https://ps.w.org/spotmaniac-dog-pedigree/assets/screenshot-3.png)

**Settings, including the editor for the dropdown values**

![Admin settings page with a sortable repeater for editing the colour, size, and breeding status options](https://ps.w.org/spotmaniac-dog-pedigree/assets/screenshot-4.png)

**Dog list with custom columns**

![Admin list of dogs with columns for photo, sex, colour, birth date, and registration number](https://ps.w.org/spotmaniac-dog-pedigree/assets/screenshot-5.png)

## Requirements

| | |
| --- | --- |
| WordPress | 6.0 or later |
| PHP | 8.0 or later |
| Tested up to | WordPress 7.0 |

## Installation

Install "Spotmaniac Dog Pedigree" from Plugins > Add New in the WordPress admin,
or upload the plugin folder to `wp-content/plugins/` and activate it.

To run this repository as the plugin itself, clone it into
`wp-content/plugins/spotmaniac-dog-pedigree/`. The repository root is the plugin
root, so no build step is required for development.

## Getting started

1. Activate the plugin. A **Dogs** menu appears in the admin sidebar.
2. Open **Dogs > Add New Dog**. Registered Name and Sex are required; every
   other field is optional.
3. Publish. The dog gets a public page at `/dogs/dog-name/`, and the archive at
   `/dogs/` lists everything published.
4. Add the parents. Once both parents exist as dogs, open the puppy and pick
   them in the **Parents** box.
5. Visit **Dogs > Settings** to set the URL prefix and the dropdown values used
   for colour, size, and breeding status.

A built-in reference lives at **Dogs > Help** inside the admin.

## What is stored for each dog

The post type registers `title` and `thumbnail` support only. Everything below
is stored as post meta under the same key, and the plugin deliberately keeps
`show_in_rest` off for the post type so the data is served through its own
namespaced API instead.

| Group | Fields |
| --- | --- |
| Identity | `dogped_name`, `dogped_call_name`, `dogped_sex`, `dogped_color`, `dogped_size`, `dogped_description` |
| Dates | `dogped_birth_date`, `dogped_death_date` |
| Registration | `dogped_registration_number`, `dogped_registration_date`, `dogped_tattoo_number`, `dogped_microchip`, `dogped_club_number` |
| Breeding | `dogped_breeding_status`, `dogped_titles`, `dogped_health` |
| People | `dogped_breeder`, `dogped_owner`, `dogped_owner_id` |
| Pedigree | `dogped_father_id`, `dogped_mother_id` |
| Media | `dogped_photo` |

Not all of it is public. Requests from users without editing rights are filtered
down to the 17 fields considered public, which leaves internal notes and owner
identity out of anonymous API responses.

Extra dropdown fields can be defined in the settings without code. Each one is
stored as `dogped_custom_{slug}`.

## Publishing dogs on the site

### Templates

The plugin ships `archive-dog.php`, `single-dog.php`, and `content-dog-card.php`,
and loads them through `template_include`. A theme can replace any of them by
providing a file with the same name inside a `spotmaniac-dog-pedigree/` folder:

```
wp-content/themes/your-theme/spotmaniac-dog-pedigree/single-dog.php
```

A theme copy always wins, so upgrades never overwrite customised output.

### Shortcodes

```
[dogped-catalog count="12" sex="" orderby="title" show_filters="yes"]
```

A paginated catalogue with optional search and filter controls.

| Attribute | Default | Notes |
| --- | --- | --- |
| `count` | Settings value, 12 | Dogs per page |
| `sex` | empty | `male` or `female` to restrict the list |
| `orderby` | Settings value, `title` | Any `WP_Query` orderby value |
| `show_filters` | `yes` | Set to `no` to hide the filter bar |

```
[dogped-featured count="6" sex=""]
```

A compact grid without filters, for a front page or sidebar.

## Settings

**Dogs > Settings** writes these options:

| Option | Default | Purpose |
| --- | --- | --- |
| `dogped_url_prefix` | `dogs` | Base of the dog permalinks |
| `dogped_catalog_count` | `12` | Default dogs per page |
| `dogped_catalog_orderby` | `title` | Default catalogue ordering |
| `dogped_photo_max_size` | `5` | Upload ceiling in megabytes |
| `dogped_section_order` | | Order of the sections on the single-dog page |
| `dogped_color_options` | | Values offered in the Colour dropdown |
| `dogped_size_options` | | Values offered in the Size dropdown |
| `dogped_breeding_status_options` | | Values offered in the Breeding Status dropdown |
| `dogped_custom_dropdown_fields` | | Definitions of admin-defined dropdown fields |

Changing the URL prefix rewrites the permalinks, so flush them afterwards by
visiting Settings > Permalinks.

## REST API

All routes live under `/wp-json/spotmaniac-dog-pedigree/v1/`.

| Method | Route | Who can call it |
| --- | --- | --- |
| `GET` | `/dog/{id}` | Anyone. Non-editors receive the public fields only |
| `GET` | `/search` | Anyone. Supports `s`, `sex`, `color`, `breeding_status`, paging |
| `GET` | `/search-parents` | Users who can create dogs. Backs the parent pickers |
| `POST` | `/dog` | Users with `edit_posts` and `publish_posts` |
| `POST` | `/dog/{id}` | Users with `edit_post` on that dog |
| `POST` | `/dog/{id}/owner-update` | The recorded owner, or an editor. Limited to a small set of fields |
| `DELETE` | `/dog/{id}` | Users with `delete_post` on that dog |

Permission callbacks check both the capability and that the target post really
is a dog, so the dog meta keys cannot be written onto unrelated posts. Writes go
through the same validation as the admin screens, including the pedigree rules.

## Hooks

### Actions

| Hook | Fires |
| --- | --- |
| `dogped_loaded` | After the plugin has booted. Add-ons hang their bootstrap here |
| `dogped_after_register_post_type` | After the dog post type is registered |
| `dogped_register_rest_routes` | While REST routes are being registered |
| `dogped_register_settings` | While settings are being registered |
| `dogped_metaboxes` | After the built-in meta boxes are added |
| `dogped_details_after_sex` | Inside the details box, under the Sex field |
| `dogped_save_metaboxes` | After a dog is saved from the admin |
| `dogped_after_save_dog_meta` | After dog meta is written, from any entry point |
| `dogped_single_badges` | In the hero area of the single-dog template |
| `dogped_single_after_hero` | Directly under the hero area |
| `dogped_single_sections` | After the built-in sections |
| `dogped_help_shortcodes`, `dogped_help_after` | Inside the admin help page |

### Filters

| Hook | Filters |
| --- | --- |
| `dogped_dog_data` | The full data array for a dog |
| `dogped_public_dog_data` | The reduced array served to visitors without editing rights |
| `dogped_section_labels` | Labels of the single-dog page sections |
| `dogped_known_shortcodes` | Shortcodes the plugin recognises |

## Uninstalling

Deleting the plugin through the WordPress admin runs `uninstall.php`, which
**permanently deletes every dog post**, bypassing the trash. Deactivating the
plugin is safe and leaves all data untouched. Export or back up before removing
it if the records still matter.

## Repository layout

```
spotmaniac-dog-pedigree.php   Plugin bootstrap, constants, version
includes/                     Post type, admin, REST API, validation, settings
assets/                       Admin and frontend CSS and JS
templates/                    Archive, single, and card templates
languages/                    Translation template
readme.txt                    WordPress.org listing and changelog
.distignore                   What is excluded from a release
```

## Releasing

WordPress.org is still served from SVN, but nothing here is committed there by
hand. Pushing a version tag runs `.github/workflows/deploy.yml`, which syncs the
repository into SVN trunk and creates the matching SVN tag.

1. Update the version in **three** places, all of which must agree:
   - the `Version:` header in `spotmaniac-dog-pedigree.php`
   - the `DOGPED_VERSION` constant in the same file
   - `Stable tag:` in `readme.txt`
2. Add a changelog entry to `readme.txt` under `== Changelog ==`, written for
   users rather than for developers.
3. Commit, then tag and push:

   ```
   git tag v1.0.3
   git push origin v1.0.3
   ```

The workflow refuses to publish if the tag disagrees with any of the three
versions, if the changelog has no entry for it, or if any PHP file fails to
parse. A bad release on WordPress.org is far more annoying to unpick than a
failed build, so it fails early on purpose.

### Required repository secrets

| Secret | What it is |
| --- | --- |
| `SVN_USERNAME` | WordPress.org account name |
| `SVN_PASSWORD` | Password for that account |

Pushing a change to the workflow file needs a token carrying the `workflow`
scope, not only `repo`.

### What reaches WordPress.org

`.distignore` decides what is excluded. Development files such as this README,
the workflow directory, and the git metadata never reach the published plugin.

## Pro add-on

[Spotmaniac Dog Pedigree Pro](https://dogspedigree.lemonsqueezy.com/) builds on
this plugin and adds the pedigree tree, a breed taxonomy, CSV import and export,
structured health tests, a breeder role with its own dashboard, a frontend
submission form, email notifications, and Schema.org output.

Pro calls shared code that lives here and refuses to load against a free plugin
older than its own minimum. When a change in this repository is required by Pro,
release this plugin first.

## License

GPLv2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
