<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Helpers\RequestHandlers;

use MichaelHall\HttpClient\HttpClientRequestInterface;
use MichaelHall\HttpClient\HttpClientResponse;
use MichaelHall\HttpClient\HttpClientResponseInterface;
use MichaelHall\HttpClient\RequestHandlers\RequestHandlerInterface;

/**
 * A test request handler used in unit tests.
 */
class TestRequestHandler implements RequestHandlerInterface
{
    /**
     * Handles the request.
     *
     * @param HttpClientRequestInterface $request The request.
     *
     * @return HttpClientResponseInterface The response.
     */
    public function handleRequest(HttpClientRequestInterface $request): HttpClientResponseInterface
    {
        switch ($request->getUrl()->getPath()) {
            case '/':
                return new HttpClientResponse(200, 'Hello World!');
            case '/foo':
                return new HttpClientResponse(200, 'This is Foo page.');
            case '/bar':
                return new HttpClientResponse(200, 'This is Bar page.');
            case '/baz':
                return new HttpClientResponse(200, 'This is Baz page.');
            case '/empty':
                return new HttpClientResponse(200, '');
        }

        return new HttpClientResponse(404, 'Page not found.');
    }
}
