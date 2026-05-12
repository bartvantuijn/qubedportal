<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Throwable;

class Setting extends Model
{
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public static function singleton(): static
    {
        try {
            return static::firstOrCreate(['id' => 1]);
        } catch (Throwable) {
            return new static(['data' => []]);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Arr::get($this->data ?? [], $key, $default);

        if (is_array($default) && is_array($value)) {
            return array_replace($default, $value);
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $data = $this->data ?? [];
        Arr::set($data, $key, $value);

        $this->update(['data' => $data]);
    }
}
