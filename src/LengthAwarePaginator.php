<?php

namespace DanBovey\LinkHeaderPaginator;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator as BasePaginator;

class LengthAwarePaginator extends BasePaginator {

	public function __construct(BasePaginator $paginator, array $options = []) {
		parent::__construct($paginator->items(), $paginator->total(), $paginator->perPage(), $paginator->currentPage(), $options);

		$this->query = $paginator->query;
		$this->path = $paginator->path;
		$this->fragment = $paginator->fragment;
		$this->pageName = $paginator->pageName;
	}

	/**
	 * The Paginator instance returns only the items
	 *
	 * @return array
	 */
	public function toArray() {
		// array_values makes sure we return an flat array
		// [0 => first, 1 => second] instead of [5 => first, 6 => second]
		return array_values($this->items->toArray());
	}

	/**
	 * Build the Link headers
	 * Can be attached to the response using `withHeaders`
	 *
	 * @return array
	 */
	public function getHeaders() {
		$links = [];

		if($this->currentPage()>1){
			$links = array_merge($links,[
				'first' => $this->url(1),
				'prev' => $this->previousPageUrl(),
			]);
		}

		if($this->currentPage()<$this->lastPage()){
			$links = array_merge($links, [
				'next' => $this->nextPageUrl(),
				'last' => $this->url($this->lastPage())
			]);
		}

		$headers = [];

		foreach($links as $rel => $url) {
			if($url != null) {
				$url = $this->joinPaths(BasePaginator::resolveCurrentPath(), $url);
				$headers[] = (new Link($url, $rel))->toString();
			}
		}

		return [
			'Link' => implode(', ', $headers),
			'X-Total-Count' => $this->total()
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