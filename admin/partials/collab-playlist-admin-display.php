<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://r.com
 * @since      1.0.0
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h2>Settings</h2>
    <form action="options.php" method="post">
        <?php
            settings_errors();
            settings_fields( $this->plugin_name );
            do_settings_sections( $this->plugin_name );
            submit_button("Save settings");
        ?>

            <!-- Spotify Connect Button -->
        <?php
            $current_url = admin_url(sprintf('admin.php?page=%s', $_GET['page']));
            $options = get_option('collab_options');
            $client_id = $options['collab_spotify_id'];
            $redirect_uri = urlencode($current_url); // Ensure this is set in App
            $scopes = urlencode(' playlist-read-private  playlist-read-collaborative  playlist-modify-public  playlist-modify-private streaming user-read-email user-read-private');
            $authorize_url = "https://accounts.spotify.com/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&show_dialog=true&scope={$scopes}";
            ?>
        <a href="<?php echo $authorize_url; ?>" class="button button-primary">Connect to Spotify</a>
        <button type="submit" id="clear-settings" class="button clear-settings">Clear Settings</button>
    </form>
</div>