# Anime ACF Plugin

**Anime ACF** is a WordPress plugin that uses the AniList API to scrape anime data and automatically fill ACF (Advanced Custom Fields) fields in a WordPress post. It also handles setting anime categories and featured images.

## Features
- Scrape anime details from AniList using the anime ID.
- Automatically populate ACF fields (e.g., title, description, episodes, etc.).
- Create anime-related WordPress categories if they don't exist.
- Set anime cover image as the post's featured image.
- Visualize anime data quickly in the WordPress editor.

## Installation
1. Clone or download the repository to your WordPress `wp-content/plugins/` directory.
2. Activate the plugin from the WordPress Admin Plugins page.
3. Ensure ACF is installed and configured for your post type.

## Usage
1. Open a WordPress post in the editor.
2. Enter an AniList anime ID in the input field above the editor.
3. Click "Scrape" to fill the post with the anime's data.

## Developer Information
This plugin includes:
- **JavaScript Integration**: A frontend script (`filler.js`) for making AJAX requests to WordPress and AniList.
- **AJAX Handling**: Secure backend communication to manage featured images and categories.
- **AniList API Integration**: Pulls data directly from AniList's GraphQL API.

## License
This project is licensed under the [MIT License](LICENSE).

## Author
- **Author**: fr0zen
- **Website**: [fr0zen.store](https://fr0zen.store)

---
