<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class PlaylistGenerator
{
    public function __construct(
        private readonly SpotifyWebAPI $api,
    ) {
    }

    /**
     * Get a new track based on the current track, choose randomly between a top song from the artist, a random song from the artist or a random related track
     *
     * @param object $track
     * @return array
     */
    public function getRandomMixTrack(object $track): array|object
    {
        $newTrack = [];
        $random = rand(0, 2);
        switch ($random) {
            case 0:
                $newTrack = $this->getRandomTopSongFromArtist($track);
                break;
            case 1:
                $newTrack = $this->getRandomSongFromArtist($track);
                break;
            case 2:
                $newTrack = $this->getRandomRelatedTrackFromTrack($track);
                break;
        }
        return $newTrack;
    }

    /**
     * Get a random top song from an artist
     *
     * @param object $track
     * @return array|object
     */
    public function getRandomTopSongFromArtist(object $track): array|object
    {
        $artist = $this->api->getArtistTopTracks($track->track->artists[0]->id, ['market' => 'FR']);

        // remove the current track from the artist top tracks
        $artist->tracks = array_filter($artist->tracks, function ($value) use ($track) {
            return $value->id != $track->track->id;
        });
        
        $randomTrack = $artist->tracks[array_rand($artist->tracks)];
        return $randomTrack;
    }

    /**
     * Get a random song from a random album of an artist
     *
     * @param object $track
     * @return array|object
     */
    public function getRandomSongFromArtist(object $track): array|object
    {
        $albums = $this->api->getArtistAlbums($track->track->artists[0]->id, ['market' => 'FR']);
        $randomAlbum = $albums->items[array_rand($albums->items)];
        $tracks = $this->api->getAlbumTracks($randomAlbum->id, ['market' => 'FR']);

        // remove the current track from the album tracks
        $tracks->items = array_filter($tracks->items, function ($value) use ($track) {
            return $value->id != $track->track->id;
        });
        
        $randomTrack = $tracks->items[array_rand($tracks->items)];
        $randomTrack->album = $randomAlbum;
        
        return $randomTrack;
    }

    /**
     * Get a random track from a related artist of the artist of the track
     *
     * @param object $track
     * @return array|object
     */
    public function getRandomRelatedTrackFromTrack(object $track): array|object
    {
        $relatedArtists = $this->api->getArtistRelatedArtists($track->track->artists[0]->id);

        // If no related artists, take the artist of the track
        if ($relatedArtists != null && count($relatedArtists->artists) > 0) {
            $randomArtist = $relatedArtists->artists[array_rand($relatedArtists->artists)];
        } else {
            $randomArtist = $track->track->artists[0];
        }

        $randomArtistTopTracks = $this->api->getArtistTopTracks($randomArtist->id, ['market' => 'FR']);
        
        //remove the current track from the artist top tracks
        $randomArtistTopTracks->tracks = array_filter($randomArtistTopTracks->tracks, function ($value) use ($track) {
            return $value->id != $track->track->id;
        });

        $newTrack = $randomArtistTopTracks->tracks[array_rand($randomArtistTopTracks->tracks)];
        return $newTrack;
    }
}
