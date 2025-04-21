<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\TicketConcern;

/**
 * App\Models\Department
 *
 * @property int $id
 * @property string $name
 * @property int $all_agents
 * @property int $public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|User[] $agent
 * @property-read int|null $agent_count
 * @property-read Collection|\App\Models\TicketConcern[] $concerns
 * @property-read int|null $concerns_count
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department query()
 * @method static Builder|Department whereAllAgents($value)
 * @method static Builder|Department whereCreatedAt($value)
 * @method static Builder|Department whereId($value)
 * @method static Builder|Department whereName($value)
 * @method static Builder|Department wherePublic($value)
 * @method static Builder|Department whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Department extends Model
{
    use HasFactory;

    public function agent(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_departments', 'department_id', 'user_id');
    }

    /**
     * Get all agents for this department
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function agents()
    {
        try {
            if (!$this->all_agents) {
                // Get only agents assigned to this department
                $agents = $this->agent()->where('status', true)->get();
                \Log::info('Department agents (specific)', [
                    'department_id' => $this->id,
                    'department_name' => $this->name,
                    'agent_count' => $agents->count(),
                    'agent_ids' => $agents->pluck('id')->toArray()
                ]);
                return $agents;
            }

            // Get all agents with dashboard access
            $agents = User::whereIn('role_id', UserRole::where('dashboard_access', true)->pluck('id'))
                ->where('status', true)
                ->get();

            \Log::info('Department agents (all)', [
                'department_id' => $this->id,
                'department_name' => $this->name,
                'agent_count' => $agents->count(),
                'agent_ids' => $agents->pluck('id')->toArray()
            ]);

            return $agents;
        } catch (\Exception $e) {
            \Log::error('Error retrieving department agents: ' . $e->getMessage(), [
                'department_id' => $this->id,
                'department_name' => $this->name,
                'exception' => $e
            ]);

            // Return empty collection as fallback
            return collect([]);
        }
    }

    /**
     * Get the concerns for the department.
     */
    public function concerns(): HasMany
    {
        return $this->hasMany(TicketConcern::class);
    }
}
