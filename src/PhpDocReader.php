<?php

namespace Loot\Otium;

use Illuminate\Support\Str;

class PhpDocReader {

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
    private function removeWrapper()
    {
        $this->comment = substr($this->reflection->getDocComment(), 3, -2);
    }

    /**
     * Split comment to array
     */
    private function splitComment()
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
        if (isset($this->lines[0]) && Str::contains($this->lines[0], '@') === false) {
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
}
