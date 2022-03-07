<?php

namespace whikloj\archivematicaPhp;

/**
 * A DjangoFilter is a fluent formatter that takes a field name and value and optional adjustment and generates
 * a field=value string.
 * ie. $t = DjangoFilter::create("myField", 2)->lessThan()->build(): // "myField__lt=2
 * @author Jared Whiklo
 * @since 0.0.1
 */
class DjangoFilter
{
    /**
     * @var string The field name.
     */
    private $field;

    /**
     * @var mixed The value
     */
    private $value;

    /**
     * @var ?string The adjustment or null for none.
     */
    private $adjustment = null;

    /**
     * Basic constructor
     * @param string $field The field name
     * @param mixed $value The value
     */
    private function __construct(string $field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * Static constructor.
     * @param string $field The field name
     * @param mixed $value The value
     * @return \whikloj\archivematicaPhp\DjangoFilter
     */
    public static function create(string $field, $value): DjangoFilter
    {
        if (!(is_int($value) || is_float($value) || is_long($value) || is_bool($value) || is_string($value))) {
            throw new \InvalidArgumentException("Value must be a string, boolean, integer, float or long.");
        }
        return new DjangoFilter($field, $value);
    }

    /**
     * Make the adjustment less than
     * @return $this
     */
    public function lessThan(): DjangoFilter
    {
        $this->adjustment = "lt";
        return $this;
    }

    /**
     * Make the adjustment greater than
     * @return $this
     */
    public function greaterThan(): DjangoFilter
    {
        $this->adjustment = "gt";
        return $this;
    }

    /**
     * Make the adjustment startsWith
     * @return $this
     */
    public function startsWith(): DjangoFilter
    {
        $this->adjustment = "startsWith";
        return $this;
    }

    /**
     * Remove any set adjustment
     * @return $this
     */
    public function clearAdjustment(): DjangoFilter
    {
        $this->adjustment = null;
        return $this;
    }

    /**
     * @return string The current field
     */
    public function getField(): string
    {
        $output = $this->field;
        if (!is_null($this->adjustment)) {
            $output .= "__" . $this->adjustment;
        }
        return $output;
    }

    /**
     * @return string The current value
     */
    public function getValue(): string
    {
        $output = "";
        if (is_bool($this->value)) {
            $output = ($this->value ? "true" : "false");
        } elseif (is_float($this->value) || is_int($this->value) || is_long($this->value)) {
            $output = $this->value;
        } elseif (is_string($this->value)) {
            $output = urlencode($this->value);
        }
        return $output;
    }

    /**
     * @return string Combine as a string.
     */
    public function build(): string
    {
        return $this->getField() . "=" . $this->getValue();
    }
}
