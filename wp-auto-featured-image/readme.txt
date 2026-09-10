=== SNY Auto Featured Image ===
Contributors: sanny_rss
Tags: auto featured image, featured image, first image, video thumbnail, bulk thumbnail
Donate link: https://sanny.dev/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=auto-featured-image&utm_content=donate
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SNY Auto Featured Image automatically sets featured images from content, external URLs, or defaults. Bulk preview & apply for existing posts.

== Description ==

**SNY Auto Featured Image** automatically sets featured images for your WordPress posts, pages, and custom post types. Extract from content, use external URLs, set category defaults, or bulk-preview and apply to existing posts.

#### Why Choose SNY Auto Featured Image?

Never publish a post without a featured image again. This lightweight plugin automatically assigns featured images based on your custom rules whenever you publish or update content.

#### Key Features

* **Conditional Image Rules** - Set specific images based on post type, category, tag, or post status.
* **Multiple Image Sources** - Choose from Media Library, extract the first image from post content, or use an external image URL.
* **Video Thumbnail Extraction** - Automatically detects embedded YouTube and Vimeo videos and extracts their thumbnails.
* **Safe Sideloading** - Optionally download and save external images directly to your WordPress Media Library.
* **Bulk Preview & Apply** - Dry-run preview of thumbnail assignments with detailed reasons before applying up to 50 posts at a time.
* **Admin Post List Column** - View featured image thumbnails directly from your post and page listing tables with configurable sizes.
* **Non-Destructive & Smart Overwrite** - Preserve existing hand-set thumbnails or selectively overwrite when needed.
* **Lightweight & Fast** - Built with native WordPress APIs, no bloat, and minimal database queries.

#### Quick Setup

1. Go to **Settings > SNY AFI**
2. In the **Image Rules** tab, add a rule selecting your image source (Media Library, First Image in Content, or External URL).
3. Choose your target post types, categories, or tags.
4. Visit **Bulk Operations** to preview and apply to existing posts, or simply publish new content!

#### Perfect For

* **Bloggers** - Ensure consistent thumbnails across all posts
* **News Sites** - Never have a missing image in article feeds
* **WooCommerce Stores** - Default product images for quick imports
* **Developers** - Clean solution for client sites

#### Documentation & Support

For detailed guides and support, visit [sanny.dev](https://sanny.dev/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=sny-auto-featured-image&utm_content=description).

Found a bug or have a feature request? [Open an issue on GitHub](https://github.com/sannysri/WordPress-Auto-Featured-Image/issues).

#### More WordPress Plugins

Check out our other WordPress plugins at [sanny.dev/plugins](https://sanny.dev/plugins/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=sny-auto-featured-image&utm_content=more_plugins).

== Installation ==
1. Upload the plugin folder to the /wp-content/plugins/ directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. Configure your rules through your WordPress admin panel in Settings => "SNY AFI".

== Screenshots ==
1. Image Rules: Configure conditional rules with Media Library, first image extraction, and taxonomy filters.
2. Bulk Preview: Interactive dry-run preview showing current vs proposed thumbnails and match reasons.
3. Settings: Configure post list thumbnail preview column and customize thumbnail sizes.
4. Post Listing: Featured image thumbnail column displaying visual previews directly on the Posts screen.

== Frequently Asked Questions ==

= How does first image and video extraction work? =

When a rule is configured with "First Image/Video", the plugin automatically scans your post content upon publish or update. It detects the first embedded image or video (YouTube and Vimeo) and sets its thumbnail as the post's featured image. You can also enable safe sideloading to download and store the media directly in your WordPress Media Library.

= Will this plugin overwrite existing featured images? =

By default, no. The plugin is strictly non-destructive and preserves existing hand-picked thumbnails. If you want a specific rule to replace existing featured images, simply check the "Overwrite existing images" option on that rule card.

= How do conditional image rules work? =

Conditional rules let you assign different images based on post type, category, tag, or post status. When saving or previewing posts, rules are evaluated from top to bottom (the first matching rule wins). Free includes up to 2 active rules.

= How does the bulk operations preview work? =

The Bulk Operations tab provides a safe dry-run preview. Before making any changes, you can click "Preview Changes" to view a table of up to 50 matching posts, comparing their current thumbnail with the proposed thumbnail, along with the matched rule or skip reason.

= Can I see featured images in the WordPress admin post list? =

Yes! In the Settings tab, enable the "Show featured image column in posts list" option. You can customize the thumbnail size (30–150px) and choose which post types display the thumbnail column.

== Changelog ==

= 2.1.0 =
* NEW: Modern tabbed admin interface (Image Rules, Bulk Operations, Settings, Help).
* NEW: Conditional image rules with multi-condition filtering (post types, categories, tags, status).
* NEW: Extract first image or YouTube/Vimeo video thumbnail from post content.
* NEW: Sideload external images into media library to prevent broken hotlinks.
* NEW: Featured image column in admin post list with configurable thumbnail size.
* NEW: Bulk assign featured images to existing posts with safe preview.
* NEW: Per-rule overwrite settings.
* IMPROVED: Performance optimizations and sanitization routines.
* Tested up to WordPress 7.1.

= 2.0.3 =
* Added review request notice after 7 days of plugin use.
* Improved readme with better description and SEO optimization.
* Added sanny.dev links for documentation and support.

= 2.0.2 =
* Tested up to WordPress 6.9.
* Updated minimum PHP requirement to 7.4.
* Added GitHub Actions for automated deployments.

= 2.0.1 =
* Fixed readme issues (short description, tags).
* Updated assets and icons.
* Stable tag updated.

= 2.0 =
* Compatibility extended up to version 6.4.2.
* Code refactoring for improved efficiency.
* Addressed minor bug fixes.

= 1.5 =
* Compatible up to 6.2.3.
* Minor bug fix.

= 1.4.1 =
* Compatible up to 4.9.5.
* Minor bug fix.

= 1.4 =
* Compatible up to 4.5.1.
* Fix media popup selector.

= 1.3 =
* Bug Fix: WHITE blank screen when choosing an image for the featured image from the plugin panel.

= 1.2 =
* Bug Fix: Featured Image Return To Default When making Changes to Content.
  [Support Topic](https://wordpress.org/support/topic/when-featured-image-is-selected-it-still-use-the-default-image)

= 1.1 =
* Restrict to specific categories.
* Allow categories for all post types or for POST only.
* Menu moved under 'Settings'.

= 1.0 =
* First release.

== Upgrade Notice ==

= 2.1.0 =
Major release: Conditional rules, first image & YouTube/Vimeo video extraction, safe media sideloading, interactive bulk preview & apply, and admin post list thumbnail column.

= 1.1 =
* Restrict to specific categories.
* Allow categories for all post types or for POST only.
* Menu moved under 'Settings'.

= 1.0 =
First Version.
