<?php

use PHPUnit\Framework\TestCase;
use DanBovey\LinkHeaderPaginator\Paginator;

class SimplePaginatorTest extends TestCase {

	private $items = [
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
		'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	];
	private $page = 2;
	private $per_page = 5;

	public function testLink() {
		$paginator = new Paginator($this->items, $this->per_page, $this->page);
		$link = $paginator->getHeaders()['Link'];
		$parsed = \phpish\link_header\parse($link);

		$this->assertEquals('/?page=' . 1, $parsed['first'][0]['uri']);
		$this->assertEquals('/?page=' . ($this->page - 1), $parsed['prev'][0]['uri']);
		$this->assertEquals('/?page=' . ($this->page + 1), $parsed['next'][0]['uri']);
	}

	public function testResponse() {
		$paginator = new Paginator($this->items, $this->per_page);
		$response = $paginator->toResponse();

		$this->assertTrue($response->headers->has('Link'));
		$this->assertEquals(json_encode(array_slice($this->items, 0, $this->per_page)), $response->content());
	}

}