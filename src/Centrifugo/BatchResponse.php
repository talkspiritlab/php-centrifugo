<?php

namespace Centrifugo;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class BatchResponse
 * @package Centrifugo
 */
class BatchResponse extends Response implements IteratorAggregate, ArrayAccess
{
    /**
     * @var Response[]
     */
    protected $responses = [];

    /**
     * BatchResponse constructor.
     *
     * @param BatchRequest $batchRequest
     * @param Response $response
     */
    public function __construct(BatchRequest $batchRequest, Response $response)
    {
        parent::__construct($batchRequest, $response->getBody());

        $this->setResponses($response->getDecodedBody());
    }

    /**
     * @param array $responses
     */
    public function setResponses(array $responses)
    {
        $this->responses = [];
        foreach ($responses as $key => $response) {
            $this->addResponse($key, $response);
        }
    }

    /**
     * Add a response to the list.
     *
     * @param int $key
     * @param array|null $response
     */
    public function addResponse($key, array $response)
    {
        $originalRequest = isset($this->request[$key]) ? $this->request[$key] : null;
        $responseBody = isset($response['body']) ? $response['body'] : null;
        $responseError = isset($response['error']) ? $response['error'] : null;
        $responseMethod = isset($response['method']) ? $response['method'] : null;

        $this->responses[$key] = new Response($originalRequest, $responseBody, $responseError, $responseMethod);
    }

    /**
     * @return Response[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @return Response
     */
    public function shiftResponses()
    {
        return array_shift($this->responses);
    }

    /***
     * @inheritdoc
     */
    public function getIterator(): \Iterator
    {
        return new ArrayIterator($this->responses);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->addResponse($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->responses[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->responses[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->responses[$offset]) ? $this->responses[$offset] : null;
    }
}
