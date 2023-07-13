<?php

declare(strict_types=1);

namespace Cundd\Rest\Domain\Model;

use InvalidArgumentException;

class Format
{
    public const DEFAULT_FORMAT = 'json';
    public const MIME_TYPES = [
        'txt'   => 'text/plain',
        'html'  => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'application/xml',
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'csv'   => 'text/csv',
        // images
        'png'   => 'image/png',
        'jpe'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'gif'   => 'image/gif',
        'bmp'   => 'image/bmp',
        'ico'   => 'image/vnd.microsoft.icon',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'svg'   => 'image/svg+xml',
        'svgz'  => 'image/svg+xml',
        // archives
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar-compressed',
        // adobe
        'pdf'   => 'application/pdf',
    ];

    private string $format;

    public function __construct(string $format)
    {
        $this->assertValidFormat($format);
        $this->format = $format;
    }

    /**
     * Return an instance of the default format
     *
     * @return Format
     */
    public static function defaultFormat(): Format
    {
        return new static(static::DEFAULT_FORMAT);
    }

    /**
     * Return HTML Format instance
     *
     * @return Format
     */
    public static function formatHtml(): Format
    {
        return new static('html');
    }

    /**
     * Return a JSON Format instance
     *
     * @return Format
     */
    public static function formatJson(): Format
    {
        return new static('json');
    }

    public function __toString()
    {
        return $this->format;
    }

    /**
     * Return if the given format is valid
     *
     * @param $format
     * @return bool
     */
    public static function isValidFormat($format): bool
    {
        if (!$format) {
            return false;
        }

        $mimeTypes = self::MIME_TYPES;

        return isset($mimeTypes[$format]);
    }

    private static function assertValidFormat(string $format): void
    {
        if (!static::isValidFormat($format)) {
            throw new InvalidArgumentException(sprintf('Invalid format "%s"', $format));
        }
    }
}
