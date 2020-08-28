<?php

namespace DanBovey\LinkHeaderPaginator;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Routing\UrlGenerator;

class Paginator extends BasePaginator {

	/**
	 * The Paginator instance returns only the items
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->items->toArray();
	}

	/**
	 * Build the Link headers
	 * Can be attached to the response using `withHeaders`
	 *
	 * @return array
	 */
	public function getHeaders() {
		$links = [
			'first'=> $this->url(1),
			'next' => $this->nextPageUrl(),
			'prev' => $this->previousPageUrl()
		];

		$headers = [];

		foreach($links as $rel => $url) {
			if($url != null) {
				$url = $this->joinPaths(BasePaginator::resolveCurrentPath(), $url);
				$headers[] = (new Link($url, $rel))->toString();
			}
		}

		return [
			'Link' => implode(', ', $headers)
		];
	}

	/**
	 * Create a Laravel Response that sends the items in the body and
	 * pagination info in the headers
	 *
	 * @return JsonResponse
	 */
	public function toResponse() {
		$response = new JsonResponse($this->toArray());

		return $response->withHeaders($this->getHeaders());
	}

	private function joinPaths($a, $b) {
		return rtrim($a, '/') .'/'. ltrim($b, '/');
	}

}