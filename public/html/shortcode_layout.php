<?php

/**
 * Provide Shortcode View
 */
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Document</title>
</head>
<body>
    <div class="container text-center collab-spotify">
        <div class="row" id="row">
            <div class="col-sm-6">
                <div class="container-search h-100 border border-success-subtle rounded-2">
                    <div id="container-search-header">
                        <div id="headline" class="row row-cols-auto align-items-center m-2">
                            <img id="logo" class="col-2 img-fluid" src="https://i.imgur.com/tRm79qz.png" alt="spotify-logo">
                            <h1 class="col-8" >Spotify Search</h1>
                        </div>
                        <div class="form-floating text-truncate mx-2">
                            <input type="text" class="form-control" id="user-input" placeholder="search for a song">
                            <label for="user-input" >search for a song</label>
                        </div>
                        <div class="container text-center m-2">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <button type="button" id="submit-btn"  class="btn btn-custom-primary btn-block w-100"><i class="bi bi-search"></i> Submit</button>
                                </div>
                                <div class="col-4">
                                    <button type="button" id="more"  class="btn btn-custom-primary btn-block w-100"><i class="bi bi-music-note-list"></i> More</button>
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div id="container-search-body" class="scrollbar">
                        <ol id="results-list" class="list-group list-group-flush">
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="container-playlist h-100 rounded-2" style="background-color: #242424; color: #fff;">
                    <div id="container-playlist-header" class="rounded-2" style="background-color: #282828;">
                        <div  class="row row-cols-auto align-items-center m-0">
                            <div class="col-2 text-start mt-2">
                                    <a id="playlist-img-link" target="_blank" rel="noreferrer noopener">
                                        <img id="playlist-img" class="img-fluid rounded-2">
                                    </a>
                            </div>
                            <div class="col-8 text-center flex-grow-1">
                                <h2 id="playlist-name"></h2>
                            </div>
                        </div>
                    </div>
                    <div id="container-playlist-body" class="scrollbar scrollbar_dark">
                        <ol id="playlist-tracks" class="list-group list-group-flush">
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="index.js"></script>
    
</body>
</html>