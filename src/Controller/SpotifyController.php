<?php

namespace App\Controller;

use Psr\Cache\CacheItemPoolInterface;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIAuthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SpotifyController extends AbstractController
{
    public function __construct(
        private readonly SpotifyWebAPI $api, 
        private readonly Session $session,
        private readonly CacheItemPoolInterface $cache,
        )
    {
    }

    // Show all playlists
    #[Route('/', name: 'app_spotify')]
    public function index(): Response
    {
        // If no access token in cache, redirect to Spotify login
        if (!$this->cache->hasItem('spotify_access_token')) {
            return $this->redirectToRoute('app_spotify_redirect');
        }

        // Set access the access token from cache
        $this->api->setAccessToken($this->cache->getItem('spotify_access_token')->get());

        // Get all user playlists
        $myPlaylists = $this->api->getMyPlaylists();
        
        return $this->render('spotify/index.html.twig', [
            'playlists' => $myPlaylists->items,
        ]);
    }

    // Show a playlist based on its ID
    #[Route('/show/{id}', name: 'app_playlist_show')]
    public function show(string $id){
        // Get the playlist
        $playlist = $this->api->getPlaylist($id);
        // Get the number of tracks in the playlist
        $total = $playlist->tracks->total;

        // Get all the tracks of the playlist with only the name, artists, album and duration
        $tracks = [];
        for($offset = 0; $offset < $total; $offset += 100){
            $tracks = array_merge($tracks, $this->api->getPlaylistTracks($id, ['fields' => 'items(track(name), track(artists), track(album), track(duration_ms))', 'offset' => $offset, 'limit'=> 100 ])->items);
        }

        $currentPlaylistTracks = $this->cache->getItem('currentPlaylistTracks');
        $currentPlaylistTracks->set($tracks);
        $currentPlaylistTracks->expiresAfter(3600);
        $this->cache->save($currentPlaylistTracks);

        return $this->render('spotify/show.html.twig',
        [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }

    // Generate a new playlist based on the artists related to the artists of the tracks of the playlist
    #[Route('/generate/{id}/{name}', name: 'app_playlist_generate')]
    public function generateNewPlaylist(string $id, string $name){
        
        // Get the tracks of the playlist from cache
        $tracks = $this->cache->getItem('currentPlaylistTracks')->get();

        // For each track, get the related artists, get a random artist and get a random track of this artist
        $newPlaylist = [];
        $newPlaylistId = [];
        foreach ($tracks as $track) {
            $relatedArtists = $this->api->getArtistRelatedArtists($track->track->artists[0]->id);
            // If no related artists, take the artist of the track
            if($relatedArtists != null && count($relatedArtists->artists) > 0){
                $randomArtist = $relatedArtists->artists[array_rand($relatedArtists->artists)];
            }
            else{
                $randomArtist = $track->track->artists[0];
            }
            $randomArtistTopTracks = $this->api->getArtistTopTracks($randomArtist->id, ['market' => 'FR']);
            $newTrack = $randomArtistTopTracks->tracks[array_rand($randomArtistTopTracks->tracks)];
            $newPlaylist[] = array("track" => $newTrack);
            $newPlaylistId[] = $newTrack->id;
        }
        
        // Save the new playlist in cache
        $cachePlaylistTracks = $this->cache->getItem('newPlaylistTrack');
        $cachePlaylistTracks->set($newPlaylistId);
        $cachePlaylistTracks->expiresAfter(3600);
        $this->cache->save($cachePlaylistTracks);

        // Save the name of the new playlist in cache
        $cachePlaylistName = $this->cache->getItem('newPlaylistName');
        $cachePlaylistName->set($name . ' - ' . date('d-m-Y H:i'));
        $cachePlaylistName->expiresAfter(3600);
        $this->cache->save($cachePlaylistName);

        return $this->render('spotify/new.html.twig',
        [
            'tracks' => $newPlaylist,
            'name' => $name
        ]);
    }

    // Create the new playlist
    #[Route('/create', name: 'app_playlist_create')]
    public function createNewPlaylist(){

        // Get the tracks and the name of the new playlist from the cache
        $newTracksId = $this->cache->getItem('newPlaylistTrack')->get();
        $newPlaylistName = $this->cache->getItem('newPlaylistName')->get();

        $this->api->setAccessToken($this->cache->getItem('spotify_access_token')->get());

        // Create the new playlist
        $newPlaylist = $this->api->createPlaylist([
            'name' => $newPlaylistName,
            'description' => "A random playlist based on $newPlaylistName",
            'public' => false,
        ]);

        // Add the tracks to the new playlist by batch of 100
        $tracksToAdd = array_chunk($newTracksId, 100);
        foreach($tracksToAdd as $tracks){
            $this->api->addPlaylistTracks($newPlaylist->id, $tracks);
        }

        //$this->api->addPlaylistTracks($newPlaylist->id, $newTracksId);

        return $this->redirectToRoute('app_spotify');
    }

    #[Route('/callback', name: 'app_spotify_callback')]
    public function callbackFromSpotify(Request $request): Response
    {
        try {
            $this->session->requestAccessToken($request->query->get('code'));
        } catch (SpotifyWebAPIAuthException $e) {
            return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $cacheItem = $this->cache->getItem('spotify_access_token');
        $cacheItem->set($this->session->getAccessToken());
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        $this->session->getAccessToken();
        return $this->redirectToRoute('app_spotify');
    }

    #[Route('/redirect', name: 'app_spotify_redirect')]
    public function redirectToSpotify(): Response
    {
        $options = [
            'scope' => [
                'user-read-email',
                'user-read-private',
                'playlist-read-private',
                'playlist-modify-public',
                'playlist-modify-private',
            ],
        ];

        return $this->redirect($this->session->getAuthorizeUrl($options));
    }

    
}
