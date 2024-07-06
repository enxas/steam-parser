# Steam parser for game pages

This library takes in scraped Steam game page HTML and returns neatly parsed data for consumption

### Example usage:
```php
<?php

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;


$cookieJar = CookieJar::fromArray([
	'birthtime' => -2208994788,
	'lastagecheckage' => '1-January-1900',
	'wants_mature_content' => 1,
], 'store.steampowered.com');

$client = new Client([
	'headers' => [
		'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
	],
	'cookies' => $cookieJar,
]);

$response = $client->get('https://store.steampowered.com/app/1284210/Guild_Wars_2/');
$contents = (string) $response->getBody();

$steaGamemData = Enxas\SteamParser::parse($contents);
```

### Output:
```php
 array:12 [▼
  "app_id" => "1284210"
  "title" => "Guild Wars 2"
  "description" => "Guild Wars 2 is an award-winning online roleplaying game with fast-paced action combat, deep character customization, and no subscription fee required. Choose from an arsenal of professions and weapons, explore a vast open world, compete in PVP modes and more. Join over 16 million players now!"
  "release_date" => "23 Aug, 2022"
  "tags" => array:20 [▶]
  "developers" => array:1 [▶]
  "publishers" => array:1 [▶]
  "franchise" => "Guild Wars"
  "features" => array:4 [▶]
  "header_image" => "https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1284210/header.jpg?t=1717542711"
  "images" => array:17 [▶]
  "videos" => array:3 [▶]
]
```