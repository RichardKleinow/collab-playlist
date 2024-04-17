# Spotify Playlist WordPress Plugin

This WordPress plugin allows administrators to connect to their Spotify account, enter their playlist details, and provide front-end users the ability to search for tracks and add them to the specified playlist without prompting for Spotify credentials.

## Features

- Connect to Spotify using the Authorization Code Flow.
- Search for tracks using the Spotify Web API.
- Add tracks to a predefined Spotify playlist.
- Display a Spotify-like track list within your WordPress site.

## Getting Started

To get this plugin up and running on your WordPress site, follow these steps:

### Prerequisites

- A WordPress website where you can install plugins.
- A Spotify Premium account.
- Basic knowledge of WordPress plugin activation and configuration.

### Installation

1. Download the plugin from the GitHub repository or clone it using:

> git clone <repository-url>


2. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.

3. Activate the plugin through the 'Plugins' screen in WordPress.

### Setting Up Spotify Developer Application

Before configuring the plugin, you need to set up an application in Spotify:

1. Go to the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/).
2. Log in with your Spotify account.
3. Click on 'Create an App'.
4. Fill in the form with your application details.
5. Once the application is created, note down the `Client ID` and `Client Secret`.
6. In the app settings, add the Redirect URI. This should match the one specified in the plugin settings and be a valid callback endpoint on your website (e.g., `https://yourwebsite.com/spotify-callback`).

### Configuring the Plugin

1. Navigate to the plugin settings in your WordPress admin panel.
2. Enter the `Client ID`, `Client Secret`, and the `Redirect URI` you've set in the Spotify Developer Dashboard.
3. Save the settings, which should establish a connection to Spotify.

### Usage

- Use the provided shortcode `[spotify_track_search]` in your pages or posts to allow users to search for tracks.
- When a track is found, users can add it to the specified playlist via a button.

## Customization

You can customize the appearance of the search results by adding custom CSS to your theme to match the style of Spotify's embedded player.

## Contributions

Contributions are welcome! If you would like to contribute to this plugin, please submit a pull request or create an issue on this repository.

## License

This project is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html). Please see the LICENSE file for more details.

## Acknowledgments

- Thanks to Spotify for providing the Web API that made this plugin possible.
- Shoutout to all the contributors who spend time improving open-source software.

---

**Note:** This plugin is not affiliated with or endorsed by Spotify. Spotify is a trademark of Spotify AB.
