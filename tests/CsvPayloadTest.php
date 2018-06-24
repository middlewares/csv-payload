<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use InvalidArgumentException;
use Middlewares\CsvPayload;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CsvPayloadTest extends TestCase
{
    public function testOptions()
    {
        // Mock
        $payload = (new CsvPayload())
            ->delimiter('|')
            ->enclosure('"')
            ->escape('\\')
            ->header(0);

        $document = implode("\n", [
            'Name|Position',
            '"Adrian Adams"|accountant',
            '"Fred Franklin"|janitor',
            '"Sally Smith"|engineer',
        ]);

        $request = $this->makeRequest('POST', $document);

        // Execute & Verify
        $this->dispatch($request, [
            $payload,
            function (Request $request) {
                $expected = [
                    ['Name' => 'Adrian Adams', 'Position' => 'accountant'],
                    ['Name' => 'Fred Franklin', 'Position' => 'janitor'],
                    ['Name' => 'Sally Smith', 'Position' => 'engineer'],
                ];
                $this->assertSame($expected, $request->getParsedBody());
            }
        ]);
    }

    public function dataInvalidControlCharacter(): array
    {
        return [
            'too long' => ['coucou'],
            'too short' => [''],
            'unicode char' => ['💩'],
            'unicode char PHP7 notation' => ["\u{0001F4A9}"],
        ];
    }

    /**
     * @dataProvider dataInvalidControlCharacter
     */
    public function testInvalidDelimiter(string $control)
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('delimiter');

        // Execute
        (new CsvPayload())->delimiter($control);
    }

    /**
     * @dataProvider dataInvalidControlCharacter
     */
    public function testInvalidEnclosure(string $control)
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('enclosure');

        // Execute
        (new CsvPayload())->enclosure($control);
    }

    /**
     * @dataProvider dataInvalidControlCharacter
     */
    public function testInvalidEscape(string $control)
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('escape');

        // Execute
        (new CsvPayload())->escape($control);
    }

    public function testInvalidHeader()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('header');

        // Execute
        (new CsvPayload())->header(-1);
    }

    private function makeRequest(string $method, string $body, string $contentType = 'text/csv'): Request
    {
        $request = Factory::createServerRequest([], $method)
            ->withHeader('Content-Type', $contentType);

        $request->getBody()->write($body);

        return $request;
    }

    private function dispatch(Request $request, array $middleware): Response
    {
        return Dispatcher::run($middleware, $request);
    }
}
