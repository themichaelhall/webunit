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
        $responseCode = 200;
        $responseText = '';
        $responseHeaders = [];

        switch ($request->getUrl()->getPath()) {
            case '/':
                $responseText = 'Hello World!';
                break;

            case '/foo':
                $responseText = 'This is Foo page.';
                $responseHeaders[] = 'X-Foo: X-Bar';
                break;

            case '/bar':
                $responseText = 'This is Bar page.';
                break;

            case '/baz':
                $responseText = 'This is Baz page.';
                break;

            case '/empty':
                break;

            default:
                $responseCode = 404;
                $responseText = 'Page not found.';
                break;
        }

        $response = new HttpClientResponse($responseCode, $responseText);
        foreach ($responseHeaders as $responseHeader) {
            $response->addHeader($responseHeader);
        }

        return $response;
    }
}
