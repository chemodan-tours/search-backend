<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class MaxTourists implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Create a new rule instance.
     *
     * @param string $dep
     * @param int $max
     */
    public function __construct(
        private string $dep = '',
        private int $max = 7,
    ) {}

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $another_value = data_get($this->data, $this->dep);

        if (is_null($another_value))
            return throw new \InvalidArgumentException("Field {$this->dep} doesn't exist.");

        return $this->max - $value >= $another_value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be less value.';
    }
}
