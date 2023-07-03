<?php

namespace Plugins\LaravelConfig\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    const CACHE_KEY_PREFIX = 'item_key:';

    use HasFactory;
    use SoftDeletes;
    use Traits\ConfigServiceTrait;

    protected $guarded = [];

    protected $casts = [
        'is_multilingual' => 'bool',
        'is_api' => 'bool',
        'is_custom' => 'bool',
        'is_enable' => 'bool',
    ];

    public function getItemValueAttribute($value)
    {
        if (in_array($this->item_type, ['array', 'json', 'object'])) {
            $value = json_decode($value, true) ?: [];
        } else if (in_array($this->item_type, ['boolean'])) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else if ($this->item_type === 'string') {
            $value = strval($value);
        } else if ($this->item_type === 'number') {
            $value = intval($value);
        } else {
            $value = $value;
        }

        return $value;
    }

    public function setItemValueAttribute($value)
    {
        if (in_array($this->item_type, ['array', 'json', 'object']) || is_array($value)) {
            if (is_null($value)) {
                $value = match ($this->item_type) {
                    default => $value = '{}',
                    'array' => $value = '[]',
                    'json', 'object' => $value = '{}',
                };
            }

            if (!is_string($value)) {            
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
        }

        if ($this->item_type === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        if ($this->item_type === 'number') {
            $value = intval($value);
        }

        if ($this->item_type === 'string') {
            $value = strval($value);
        }

        $this->attributes['item_value'] = $value;
    }
}
