<?php
namespace Gt\Fetch;

use Gt\Curl\JsonDecodeException;
use Gt\Http\Header\ResponseHeaders;
use Gt\Http\Response;
use Gt\Http\StatusCode;
use Psr\Http\Message\UriInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use stdClass;

/**
 * @property-read ResponseHeaders $headers
 * @property-read bool $ok
 * @property-read bool $redirected
 * @property-read int $status
 * @property-read string $statusText
 * @property-read string $type
 * @property-read UriInterface $uri
 * @property-read UriInterface $url
 */
class BodyResponse extends Response {
	/** @var Deferred */
	protected $deferred;

	public function arrayBuffer():TODO {
	}

	public function blob():TODO {
	}

	public function formData():TODO {
	}

	public function json(int $depth = 512, int $options = 0):StdClass {
		$deferred = new Deferred();

		$json = json_decode(
			$this->getBody(),
			false,
			$depth,
			$options
		);
		if(is_null($json)) {
			$errorMessage = json_last_error_msg();
			throw new JsonDecodeException($errorMessage);
		}

		$deferred->resolve($json);

		return $deferred->promise();
	}

	public function text():Promise {
		$this->deferred = new Deferred();
		$promise = $this->deferred->promise();
		return $promise;
	}

	public function completeResponse():void {
		$position = $this->stream->tell();
		$this->stream->rewind();
		$contents = $this->stream->getContents();
		$this->stream->seek($position);
		$this->deferred->resolve($contents);
	}

	public function __get(string $name) {
		switch($name) {
		case "headers":
			return $this->getHeaders();
			break;

		case "ok":
			return ($this->statusCode >= 200
				&& $this->statusCode < 300);

		case "redirected":
			break;

		case "status":
			return $this->getStatusCode();

		case "statusText":
			return StatusCode::REASON_PHRASE[$this->status];

		case "type":
			// TODO: What exactly is this property for?
			break;

		case "uri":
		case "url":
			// TODO: How do we get the URI from the request?
			break;
		}
	}
}