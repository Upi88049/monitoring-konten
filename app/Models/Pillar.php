<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pillar extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Ambil semua nama pilar yang ada di database.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return static::orderBy('name', 'asc')->pluck('name')->toArray();
    }

    /**
     * Ambil semua pilar sebagai objek dengan properti ->value agar kompatibel dengan template Blade.
     *
     * @return array<int, object{value: string}>
     */
    public static function allPillars(): array
    {
        return static::orderBy('name', 'asc')->get()->map(function ($p) {
            return (object) ['value' => $p->name];
        })->all();
    }
}
