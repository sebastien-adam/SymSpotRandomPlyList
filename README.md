<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/sebastien-adam/SymSpotRandomPlyList">
    <img src="images/logo.png" alt="No Logo for now" width="80" height="80">
  </a>

<h3 align="center">Spotify Random Clone PLaylist Generator</h3>

  <p align="center">
    Generates a playlist variant based on a Spotify user playlist
    <br />
    <a href="https://github.com/sebastien-adam/SymSpotRandomPlyList"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/sebastien-adam/SymSpotRandomPlyList">View Demo</a>
    ·
    <a href="https://github.com/sebastien-adam/SymSpotRandomPlyList/issues/new?labels=bug&template=bug-report---.md">Report Bug</a>
    ·
    <a href="https://github.com/sebastien-adam/SymSpotRandomPlyList/issues/new?labels=enhancement&template=feature-request---.md">Request Feature</a>
  </p>
</div>



> [!WARNING]
> v0.1.0 WIP : This project is still in development, the main feature is working but the project is not yet fully functional and I will add more in the future.

<!-- ABOUT THE PROJECT -->
## About The Project

<!-- [![Product Name Screen Shot][product-screenshot]](https://example.com) -->

A missing feature in Spotify is the ability to generate a random playlist based on a user playlist. This project aims to fill this gap by providing a Symfony application that generates a random playlist based on a user playlist.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

* [Symfony](https://symfony.com/)

## Getting Started

To get a local copy up and running follow these simple example steps.

### Prerequisites

First, you must register your application at https://developer.spotify.com/dashboard/applications to obtain the `client_id` and `client_secret`.  
You need to register the redirect_uri in the Spotify dashboard to https://127.0.0.1:8000/callback/ for example.

Also make sure you have the following installed on your computer:

* PHP 8.2
* Composer

### Installation

1. Clone the repo

   ```sh
   git clone git@github.com:sebastien-adam/SymSpotRandomPlyList.git
   ```

2. Install Composer packages

   ```sh
   composer install
   ```

3. Enter your API credentials in `.env.local`

   ```sh
   ###> SPOTIFY API ### 
    SPOTIFY_CLIENT_ID="CLIENT_ID"
    SPOTIFY_CLIENT_SECRET="CLIENT_SECRET"
    SPOTIFY_CLIENT_URI="http://127.0.0.1:8000/callback"
    ###< SPOTIFY API ###

4. Run the server

   ```sh
   symfony server:start
   ```

5. Open your browser and go to `http://127.0.0.1:8000/` and authorize the application to access your Spotify account.
6. Enjoy the application

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<p align="right">(<a href="#readme-top">back to top</a>)</p>
