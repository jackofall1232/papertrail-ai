=== PaperTrail AI — Smart Document Library ===
Contributors: askadam
Tags: document library, file manager, AI search, PDF, OpenAI
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered document library for WordPress. Organize files and search them semantically using OpenAI embeddings.

== Description ==

PaperTrail AI is a smart document library for WordPress. It lets you upload, organize, and search documents (PDFs, Word, spreadsheets, images, and more) using a dedicated custom post type, taxonomies, and an optional AI-powered semantic search powered by OpenAI embeddings.

PaperTrail AI is part of the **Ask Adam plugin suite**.

= Free features =

* Dedicated "Files" custom post type for documents
* Categories and tags for organizing your library
* Frontend archive and single views
* Shortcode and Gutenberg block for embedding files anywhere
* Classic keyword search across your library
* Optional AI-powered semantic search via your own OpenAI API key
* Admin columns for quick file management

= Pro features (separate plugin) =

PaperTrail AI Pro extends the free version with deeper Ask Adam Pro integration:

* Conversational document Q&A
* Cross-document context retrieval
* Bulk embedding generation
* Advanced analytics
* Priority support

Pro is sold and distributed separately as part of the Ask Adam Pro suite.

== Installation ==

1. Upload the `papertrail-ai` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to **Files → Settings** to configure the plugin.
4. (Optional) Add your OpenAI API key to enable AI semantic search.
5. Add your first document under **Files → Add New**.

== Frequently Asked Questions ==

= Do I need an OpenAI API key to use this plugin? =

No. PaperTrail AI works as a full document library without any API key. The OpenAI API key is only required to enable AI-powered semantic search.

= Is my OpenAI API key stored securely? =

Your API key is stored in the WordPress options table and is only accessible to administrators. We recommend restricting your OpenAI API key to the minimum required scopes.

= What file types are supported? =

Any file type that WordPress accepts in the media library can be attached to a PaperTrail AI file entry, including PDFs, Word documents, spreadsheets, images, and more.

= How do I display files on the frontend? =

You can use the `[papertrail_ai]` shortcode, the PaperTrail AI Gutenberg block, or visit the file archive page directly.

== Changelog ==

= 1.0.0 =
* Initial release.
* Custom post type and taxonomy for documents.
* Classic and AI-powered search.
* Shortcode and Gutenberg block.
* Settings page with OpenAI integration.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
