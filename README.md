# custom-plugin
Adds category filters in the WordPress dashboard and other features.
# Custom Plugin

**Description in English**

This is a custom WordPress plugin that adds several features, including:

- Category and subcategory filters in the admin panel.
- Featured image column in the posts list.
- Access restrictions to specific categories for unauthenticated users.
- Integration with an external API to automatically create posts based on received data.

## Installation

1. Download the plugin or clone the repository to the `wp-content/plugins` directory.
2. Activate the plugin in the WordPress admin panel under **Plugins > Installed Plugins**.
3. Make sure to set up the required environment variables for the external API:
   - `API_BEARER_TOKEN`: authentication token for the external API.

## Usage

- **Category filters:** In the admin panel, you'll see new category and subcategory filters when managing posts and custom post types.
- **Featured image column:** The featured image column will automatically appear in the posts list, displaying a thumbnail of the image if available.
- **API Integration:** The plugin will periodically call the configured API to automatically create posts based on received data.

## Features

- Category and subcategory filters in custom post types.
- Featured image column in the admin area.
- Access restrictions to specific pages.
- Automatic post creation with data from an external API.

## Contributions

Contributions are welcome! If you encounter an issue or want to suggest an improvement, feel free to open an issue or submit a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
