<?php

namespace Loot\Otium;

use Illuminate\Support\Str;

class PhpDocReader {

    public const FIELDS = [
        'extra' => '@param-otium-extra',
    ];

    /**
     * @var \ReflectionMethod
     */
    private $reflection;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string[]
     */
    private $lines;

    /**
     * PhpDocReader constructor.
     * @param mixed $class
     * @param string $methodName
     * @throws \Exception
     */
    public function __construct($class, string $methodName)
    {
        $this->reflection = new \ReflectionMethod(...func_get_args());
        $this->removeWrapper();
        $this->splitComment();
    }

    /**
     * removes comment tags
     */
    private function removeWrapper(): void
    {
        $this->comment = substr($this->reflection->getDocComment(), 3, -2);
    }

    /**
     * Split comment to array
     */
    private function splitComment(): void
    {
        $lines = explode("\n", $this->comment);
        /** remove spaces and wildcard */
        $lines = array_map([$this, 'cleanUpLine'], $lines);
        /** remove blank lines */
        $lines = array_filter($lines);
        /** reindex array */
        $this->lines = array_values($lines);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        if (isset($this->lines[0]) && $this->lineHasParam($this->lines[0]) === false) {
            return $this->lines[0];
        }

        return '';
    }

    /**
     * @param string $line
     * @return string
     */
    public function cleanUpLine(string $line) {
        return trim(
            str_replace('*', '', $line)
        );
    }

    /**
     * @param string $line
     * @param string $type
     * @return bool
     */
    private function lineHasParam(string $line, string $type = '@'): bool
    {
        return Str::startsWith($line, $type);
    }

    /**
     * Find @param-otium-extra annotation.
     * @return array
     */
    public function getExtraFields(): array
    {
        $result = [];
        $extraFields = array_filter($this->lines, function($line) {
           return $this->lineHasParam($line, self::FIELDS['extra']);
        });

        foreach ($extraFields as $extraField) {
            [, $json] = explode(self::FIELDS['extra'], $extraField);
            $jsonArray = json_decode(trim($json), true);
            $result += $jsonArray ?? [];
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $result = ['invalid json in '.self::FIELDS['extra']];
        }

        return $result;
    }
}
