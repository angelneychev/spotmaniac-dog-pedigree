# Spotmaniac Dog Pedigree

Source repository for the free [Spotmaniac Dog Pedigree](https://wordpress.org/plugins/spotmaniac-dog-pedigree/)
WordPress plugin: a dog catalogue with pedigree data, owner and breeder
records, and a public dog archive.

The Pro add-on lives outside this repository and is sold through
[dogspedigree.lemonsqueezy.com](https://dogspedigree.lemonsqueezy.com/).

## Releasing

WordPress.org is still served from SVN, but nothing here is committed there by
hand. Pushing a version tag runs `.github/workflows/deploy.yml`, which syncs
the repository into SVN trunk and creates the matching SVN tag.

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

### What gets published

`.distignore` decides what is excluded from the released plugin. Development
files such as this README and the workflow directory never reach
WordPress.org.

## Pro add-on compatibility

The Pro add-on calls shared code that lives in this plugin, and refuses to load
against a version older than its own `DOGPED_PRO_MIN_FREE_VERSION`. When a
change here is required by Pro, release this plugin first.
