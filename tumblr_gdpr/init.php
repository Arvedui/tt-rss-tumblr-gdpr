<?php
/* TT-RSS tumblr gdpr
 * Copyright (C) 2018  Arvedui <arvedui@posteo.de>

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 **/
class Tumblr_Gdpr extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Circumvent tumblr gdpr bullshit",
			"Arvedui");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_FETCH_FEED, $this);

	}

	function hook_fetch_feed($feed_data, $fetch_url, $owner_uid, $feed, $last_article_timestamp, $auth_login, $auth_pass) {
		if (!preg_match(";^https?://.*\.tumblr.com/rss$;", $fetch_url)) {
			return $feed_data;
		}

		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_COOKIEJAR, "/dev/null");
		curl_setopt($curl_handle, CURLOPT_COOKIEFILE, "/dev/null");
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);

		// Configure curl handle for acquiring the cookie
		$data = array(
			"eu_resident" => "True",
			"gdpr_consent_core" => "False",
			"gdpr_consent_first_party_ads" => "False",
			"gdpr_consent_search_history" => "False",
			"gdpr_consent_third_party_ads" => "False",
			"gdpr_is_acceptable_age" => "False",
			"redirect_to" => $url);

		curl_setopt($curl_handle, CURLOPT_URL, "https://www.tumblr.com/svc/privacy/consent");
		curl_setopt($curl_handle, CURLOPT_POST, true);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));

		curl_exec($curl_handle);

		// Configure handle for actual rss request
		curl_setopt($curl_handle, CURLOPT_POST, false);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "");
		curl_setopt($curl_handle, CURLOPT_URL, $fetch_url);

		$data = curl_exec($curl_handle);

		curl_close($curl_handle);

		return $data;

	}

	function api_version() {
		return 2;
	}
}
?>
