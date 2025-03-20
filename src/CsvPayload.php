<?php
declare(strict_types = 1);

namespace Middlewares;

use InvalidArgumentException;
use League\Csv\Reader;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;

class CsvPayload extends Payload implements MiddlewareInterface
{
    /** @var array<string> */
    protected $contentType = ['text/csv'];

    /**
     * The field delimiter (one character only).
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * The field enclosure (one character only).
     *
     * @var string
     */
    protected $enclosure = '"';

    /**
     * The field escape (one character only).
     *
     * @var string
     */
    protected $escape = '\\';

    /**
     * The offset to use as the header.
     *
     * @var int|null
     */
    protected $header;

    /**
     * Set Csv Control delimiter character
     */
    public function delimiter(string $delimiter): self
    {
        $this->delimiter = self::filterControl($delimiter, 'delimiter');

        return $this;
    }

    /**
     * Set Csv Control enclosure character
     */
    public function enclosure(string $enclosure): self
    {
        $this->enclosure = self::filterControl($enclosure, 'enclosure');

        return $this;
    }

    /**
     * Set Csv Control escape character
     */
    public function escape(string $escape): self
    {
        $this->escape = self::filterControl($escape, 'escape');

        return $this;
    }

    /**
     * Set the Csv header offset
     */
    public function header(?int $offset): self
    {
        $this->header = self::filterHeader($offset);

        return $this;
    }

    /**
     * Filter Csv control character
     */
    private static function filterControl(string $char, string $type): string
    {
        if (strlen($char) === 1) {
            return $char;
        }

        throw new InvalidArgumentException(sprintf('The %s character must be a single character', $type));
    }

    /**
     * Filter Csv header offset
     */
    private static function filterHeader(int $header): int
    {
        if ($header >= 0) {
            return $header;
        }

        throw new InvalidArgumentException('The header must be greater than or equal to zero');
    }

    /**
     * {@inheritdoc}
     * @return array<array<string, string|int|float>>
     */
    protected function parse(StreamInterface $stream): array
    {
        $csv = Reader::createFromString((string) $stream);
        $csv->setDelimiter($this->delimiter);
        $csv->setEnclosure($this->enclosure);
        $csv->setEscape($this->escape);
        $csv->setHeaderOffset($this->header);

        // Pass through array_values() to ensure that the resulting array
        // starts with an index of zero.
        return array_values(iterator_to_array($csv->getRecords()));
    }
}
