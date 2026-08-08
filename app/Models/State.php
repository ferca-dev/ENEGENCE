<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'estados';

    protected $fillable = [
        'code',
        'name',
        'abbreviation',
        'total_population',
        'female_population',
        'male_population',
        'inhabited_dwellings',
    ];

    protected function casts(): array
    {
        return [
            'total_population' => 'integer',
            'female_population' => 'integer',
            'male_population' => 'integer',
            'inhabited_dwellings' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
