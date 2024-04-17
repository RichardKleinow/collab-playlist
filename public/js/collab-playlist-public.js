(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */




    $( window ).load(function() {
        loadPlaylist();


    });


	$(function () {
		var nextUrl;
		$("#more").hide();

	
	
		function errorFunc (xhr, textStatus, thrownError) {
			console.log(xhr.responseText);
		}


        $(document).on('click', '#add-to-playlist-btn', function() {
            var trackId = $(this).attr('data-track-id'); // Get Data stored in Button
            addToPlaylist(trackId);
        });

        
        function addToPlaylist(trackId) {
            $.ajax({
                url: CollabSpotifyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'collab_spotify_add_to_playlist',
                    query: trackId,
                    nonce: CollabSpotifyAjax.nonce
                },
                success: function(data) {
                    //console.log(data);
                    loadPlaylist();
                },
                error: errorFunc
            });
        }


		function fillTracks (data) {
			var tracks = data.data.tracks;
            if (tracks && tracks.items && tracks.next) {
                var html = '';
                var imgUrl = '/default.jpg';
 

                var container = $('#results-list');

                html += tracks.items.map((item, index) => {
                    return `<li class='list-group-item border-1'>
                                <div class="track-container">
                                    <div class="row  align-items-center">
                                        <div class="col-6">
                                            <p id="search-track-artist" class='text-start text-black m0'>
                                            ${item.name}<br>
                                                <small><em>${item.artists.map(artist => artist.name).join(", ")}</em></small>
                                            </p>
                                        </div>
                                        <div class="col-2">
                                            <div class="img-container" id="img-album" >
                                                <a class='title-a' href='${item.external_urls.spotify}' target='_blank'>
                                                <img src='${item.album.images[0].url}' class='img-fluid rounded-2'></a>
                                            </div>
                                        </div>
                                        <div class="col-4 mx-auto">
                                            <button class='btn btn-custom-primary' id='add-to-playlist-btn' data-track-id='${item.id}'>Add to Playlist</button>
                                        </div>
                                    </div>
                                </div>
                            </li>`;
                }).join("");

                container.html(html);

        
                if (tracks.next != null) {
                    $("#more").show();
            
                    nextUrl = tracks.next;
                
                } else {
                    $("#more").hide();
                }
            }
		}


	
		$("#more").on("click", function() {
            $.ajax({
                url: CollabSpotifyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'collab_spotify_next_tracks',
                    query: nextUrl,
                    nonce: CollabSpotifyAjax.nonce
                },
                success: fillTracks,
                error: errorFunc
            });
		});

        function submitSearch() {
            var userInput = $('#user-input').val();

            $.ajax({
                url: CollabSpotifyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'collab_spotify_track_search',
                    query: userInput,
                    nonce: CollabSpotifyAjax.nonce
                },
                success: fillTracks,
                error: errorFunc
            });
        }

        $('#user-input').on('keypress', function (e) {
            if (e.which === 13) {
                submitSearch();
            }
        });
	
		$('#submit-btn').on('click', function () {
			submitSearch();
		});
	
	
	});


    // Universal Functions

    function errorFunc (xhr, textStatus, thrownError) {
        console.log(xhr.responseText);
    }



    function loadPlaylist() {
        $.ajax({
            url: CollabSpotifyAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'collab_spotify_load_playlist',
                nonce: CollabSpotifyAjax.nonce
            },
            success: fillPlaylist,
            error: errorFunc
        });
    }

    function fillPlaylist(data) {
        //console.log(data);
        // set name
        $('#playlist-name').text(data.data.name);

        // set image
        if (data.data.img[1].url.length > 0) {
            var imgUrl = data.data.img[1].url;
            $('#playlist-img').attr('src', imgUrl);
        }

        // set link
        if(data.data.link.length > 0) {
            $('#playlist-img-link').attr('href', data.data.link);
        }


        var tracks = data.data.tracks;
        var tracklist = $('#playlist-tracks');
        tracklist.empty();
        tracks.forEach(function(track,i) {
            var trackName = track.track.name;
            var trackDuration = msToMinutesSeconds(track.track.duration_ms);
            var trackNumber = i + 1;
            var trackArtists = track.track.artists.map(artist => artist.name).join(', ');
            var trackHtml = '<li class="list-group-item border-0" style="background-color: #242424;">' +
                                '<div class="track-container row text-white">' +
                                    '<div class="col-1 d-flex align-items-center text-left">' +
                                       '<p>' + trackNumber + '</p>' +
                                    '</div>' +
                                    '<div class="col-5 d-flex align-items-center text-left">' +
                                        '<p>' + trackName + '<br />' +  
                                        '<span class="text-white-50"><small>' + trackArtists +  '</small></span></p>' +
                                    '</div>' + 
                                    '<div class="col-6 d-flex align-items-center justify-content-end">' +
                                        '<p>' + trackDuration + '</p>' +
                                    '</div>' +
                                '</div>' +
                            '</li>';

            tracklist.append(trackHtml);
        });

    }

    function msToMinutesSeconds(milliseconds) {
        const minutes = Math.floor(milliseconds / 60000);
        const seconds = ((milliseconds % 60000) / 1000).toFixed(0);
        return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
    }


})( jQuery );



