<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\CondoLocation
 *
 * @property int $id
 * @property string $name
 * @property boolean $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|CondoLocation newModelQuery()
 * @method static Builder|CondoLocation newQuery()
 * @method static Builder|CondoLocation query()
 * @method static Builder|CondoLocation whereId($value)
 * @method static Builder|CondoLocation whereName($value)
 * @method static Builder|CondoLocation whereStatus($value)
 * @method static Builder|CondoLocation whereCreatedAt($value)
 * @method static Builder|CondoLocation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CondoLocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the users for the condo location.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
