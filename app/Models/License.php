<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'domain',
        'key',
        'verified_at',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Controleer of de licentie recent gevalideerd is.
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->verified_at && $this->verified_at->gt(Carbon::now()->subHours(24));
    }
}
