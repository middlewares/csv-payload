<?php

namespace Middlewares\Tests;

use Middlewares\JsonPayload;
use Middlewares\CsvPayload;
use Middlewares\UrlEncodePayload;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\CallableMiddleware;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class JsonPayloadTest extends \PHPUnit_Framework_TestCase
{
    public function payloadProvider()
    {
        return [
            ['application/json', '{"bar":"foo"}', ['bar' => 'foo']],
            ['application/json', '', []],
            ['application/x-www-form-urlencoded', 'bar=foo', ['bar' => 'foo']],
            ['application/x-www-form-urlencoded', '', []],
            ['text/csv', "one,two\nthree,four", [['one', 'two'], ['three', 'four']]],
        ];
    }

    /**
     * @dataProvider payloadProvider
     */
    public function testPayload($header, $body, $result)
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($body);

        $request = (new ServerRequest())
            ->withHeader('Content-Type', $header)
            ->withMethod('POST')
            ->withBody($stream);

        $response = (new Dispatcher([
            new JsonPayload(),
            new CsvPayload(),
            new UrlEncodePayload(),
            new CallableMiddleware(function ($request) use ($result) {
                $this->assertEquals($result, $request->getParsedBody());
                $response = new Response();
                $response->getBody()->write('Ok');

                return $response;
            }),
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Ok', (string) $response->getBody());
    }

    public function testError()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{invalid:"json"}');

        $request = (new ServerRequest())
            ->withHeader('Content-Type', 'application/json')
            ->withMethod('POST')
            ->withBody($stream);

        $response = (new Dispatcher([
            new JsonPayload(),
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function methodProvider()
    {
        return [
            [
                ['POST'],
                'POST',
                '{"bar":"foo"}',
                ['bar' => 'foo'],
            ], [
                ['PUT'],
                'POST',
                '{"bar":"foo"}',
                null,
            ], [
                ['GET'],
                'GET',
                '{"bar":"foo"}',
                ['bar' => 'foo'],
            ],
        ];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMethods($methods, $method, $body, $result)
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($body);

        $request = (new ServerRequest())
            ->withHeader('Content-Type', 'application/json')
            ->withMethod($method)
            ->withBody($stream);

        $response = (new Dispatcher([
            (new JsonPayload())->methods($methods),
            new CallableMiddleware(function ($request) use ($result) {
                $this->assertEquals($result, $request->getParsedBody());
                $response = new Response();
                $response->getBody()->write('Ok');

                return $response;
            }),
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Ok', (string) $response->getBody());
    }

    public function testOverride()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('{"bar":"foo"}');

        $request = (new ServerRequest())
            ->withHeader('Content-Type', 'application/json')
            ->withMethod('POST')
            ->withBody($stream);

        $response = (new Dispatcher([
            new JsonPayload(),
            new CallableMiddleware(function ($request, $next) {
                $this->assertEquals(['bar' => 'foo'], $request->getParsedBody());

                return $next->process($request->withParsedBody(['other' => 'body']));
            }),
            new JsonPayload(),
            new CallableMiddleware(function ($request, $next) {
                $this->assertEquals(['other' => 'body'], $request->getParsedBody());

                return $next->process($request);
            }),
            (new JsonPayload())->override(),
            new CallableMiddleware(function ($request, $next) {
                $this->assertEquals(['bar' => 'foo'], $request->getParsedBody());
                $response = new Response();
                $response->getBody()->write('Ok');

                return $response;
            }),

        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Ok', (string) $response->getBody());
    }
}
