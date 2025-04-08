<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Department;

/**
 * App\Models\TicketConcern
 *
 * @property int $id
 * @property string $name
 * @property boolean $status
 * @property int|null $assigned_to
 * @property int|null $department_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property-read \App\Models\User|null $assignedUser
 * @property-read \App\Models\Department|null $department
 * @method static Builder|TicketConcern newModelQuery()
 * @method static Builder|TicketConcern newQuery()
 * @method static Builder|TicketConcern query()
 * @method static Builder|TicketConcern whereId($value)
 * @method static Builder|TicketConcern whereName($value)
 * @method static Builder|TicketConcern whereStatus($value)
 * @method static Builder|TicketConcern whereCreatedAt($value)
 * @method static Builder|TicketConcern whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TicketConcern extends Model
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
        'assigned_to',
        'department_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'assigned_to' => 'integer',
        'department_id' => 'integer',
    ];

    /**
     * Get the tickets for the concern.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'concern_id');
    }

    /**
     * Get the user assigned to this concern.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the department that this concern belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
