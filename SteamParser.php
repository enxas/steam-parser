<?php

namespace Enxas;

class SteamParser {
	static private $result = [];

	static private function getAppId($finder) {
		$nodes = $finder->query("//input[@id='review_appid']/@value");

		static::$result['app_id'] = intval($nodes[0]?->nodeValue) ?? null;
	}

	static private function getDescription($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' game_description_snippet ')]");

		static::$result['description'] = trim(str_replace("\n", ' ', str_replace(["\t", "\r"], '', $nodes[0]->nodeValue)));
	}

	static private function getHeaderImage($finder) {
		$nodes = $finder->query("//img[contains(concat(' ', normalize-space(@class), ' '), ' game_header_image_full ')]/@src");

		static::$result['header_image'] = $nodes[0]->nodeValue;
	}

	static private function getTags($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' popular_tags ')]/a");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = trim($tagNode->nodeValue);
		}

		static::$result['tags'] = $data;
	}

	static private function getTitle($finder) {
		$nodes = $finder->query("//div[@id='appHubAppName']");

		static::$result['title'] = $nodes[0]->nodeValue;
	}

	static private function getDevelopers($finder) {
		$nodes = $finder->query("//div[@id='developers_list']/a");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = $tagNode->nodeValue;
		}

		static::$result['developers'] = $data;
	}

	static private function getPublishers($finder) {
		$nodes = $finder->query("//div[@id='game_highlights']//div[contains(concat(' ', normalize-space(@class), ' '), ' dev_row ')][2]//div[contains(concat(' ', normalize-space(@class), ' '), ' summary ')]/a");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = $tagNode->nodeValue;
		}

		static::$result['publishers'] = $data;
	}

	static private function getReleaseDate($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' release_date ')]//div[contains(concat(' ', normalize-space(@class), ' '), ' date ')]");
		
		static::$result['release_date'] = $nodes[0]->nodeValue;
	}

	static private function getFeatures($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' game_area_features_list_ctn ')]//a//div[contains(concat(' ', normalize-space(@class), ' '), ' label ')]");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = $tagNode->nodeValue;
		}

		static::$result['features'] = $data;
	}

	static private function getImages($finder, $appId) { 
		$nodes = $finder->query("//div[@id='highlight_player_area']//a//@data-screenshotid");

		foreach ($nodes as $tagNode) {
			[$fileName, $extension] = explode('.', $tagNode->nodeValue);
			static::$result['images'][] = array_map(
				fn ($size) => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/'.$appId.'/'.$fileName.'.'.$size.'.'.$extension, 
				['116x65', '600x338', '1920x1080']
			);
		}
	}
		
	static private function getVideos($finder) {
		$nodes = $finder->query("//div[@id='highlight_player_area']//div[contains(concat(' ', normalize-space(@class), ' '), ' highlight_movie ')]");
		
		foreach ($nodes as $tagNode) {
			static::$result['videos'][] = [
				'webm' => [
					$tagNode->getAttribute('data-webm-source'),
					$tagNode->getAttribute('data-webm-hd-source'),
				],
				'mp4' => [
					$tagNode->getAttribute('data-mp4-source'),
					$tagNode->getAttribute('data-mp4-hd-source'),
				],
				'poster' => $tagNode->getAttribute('data-poster'),
			];
		}
	}

	static private function getFranchise($finder) {
		$nodes = $finder->query("//div[@id='genresAndManufacturer']//b[text()='Franchise:']//following-sibling::a");
		
		static::$result['franchise'] = $nodes[0]?->nodeValue ?? null;
	}


	static public function parse(string $html) {
		$internalErrors = libxml_use_internal_errors(true);
		
		$dom = new \DOMDocument;
		$dom->loadHTML($html);
		$finder = new \DomXPath($dom);
		
		static::getAppId($finder);

		if (static::$result['app_id'] === null) {
			return ['error' => 'The provided HTML doesn\'t contain game data. You might have scraped a game page that requires age verification.'];
		}

		static::getTitle($finder);
		static::getDescription($finder);
		static::getReleaseDate($finder);
		static::getTags($finder);
		static::getDevelopers($finder);
		static::getPublishers($finder);
		static::getFranchise($finder);
		static::getFeatures($finder);
		static::getHeaderImage($finder);
		static::getImages($finder, static::$result['app_id']);
		static::getVideos($finder);

		libxml_use_internal_errors($internalErrors);

		return static::$result;
	}
}
