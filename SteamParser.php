<?php

namespace Enxas;

class SteamParser {
	/** @var array */
	static private $result = [];

	static private function getAppId($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' game_page_background ')]/@data-miniprofile-appid");

		static::$result['app_id'] = intval($nodes[0]?->nodeValue);
	}

	static private function getDescription($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' game_description_snippet ')]");

		static::$result['description'] = trim(str_replace("\n", ' ', str_replace(["\t", "\r"], '', $nodes[0]->nodeValue)));
	}

	static private function getTags($finder) {
		$nodes = $finder->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' popular_tags ')]/a");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = trim($tagNode->nodeValue);
		}

		static::$result['tags'] = $data;
	}

	static private function getGenres($finder) {
		$nodes = $finder->query("//div[@id='genresAndManufacturer']//b[text()='Genre:']//following-sibling::span/a");
		$data = [];

		foreach ($nodes as $tagNode) {
			$data[] = $tagNode->nodeValue;
		}

		static::$result['genres'] = $data;
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
			static::$result['images'][] = $tagNode->nodeValue;
		}
	}
		
	static private function getVideos($finder) {
		$nodes = $finder->query("//div[@id='highlight_player_area']//div[contains(concat(' ', normalize-space(@class), ' '), ' highlight_movie ')]");
		
		foreach ($nodes as $tagNode) {
			$videoUrl = parse_url($tagNode->getAttribute('data-webm-source'));
			$videoUrlParts= explode('/', $videoUrl['path'])[2];
			static::$result['videos'][] = intval($videoUrlParts);
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

		if (static::$result['app_id'] === 0) {
			return ['error' => 'The provided HTML doesn\'t contain game data. You might have scraped a game page that requires age verification.'];
		}

		static::getTitle($finder);
		static::getDescription($finder);
		static::getReleaseDate($finder);
		static::getTags($finder);
		static::getGenres($finder);
		static::getDevelopers($finder);
		static::getPublishers($finder);
		static::getFranchise($finder);
		static::getFeatures($finder);
		static::getImages($finder, static::$result['app_id']);
		static::getVideos($finder);

		libxml_use_internal_errors($internalErrors);

		return static::$result;
	}
}
