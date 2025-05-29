<?php

namespace App\Models;

use Eloquent;
use EloquentFilter\Filterable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Route;

/**
 * App\Models\UserRole
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property array|null $permissions
 * @property int $dashboard_access
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static Builder|UserRole filter($input = [], $filter = null)
 * @method static Builder|UserRole newModelQuery()
 * @method static Builder|UserRole newQuery()
 * @method static Builder|UserRole paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|UserRole query()
 * @method static Builder|UserRole simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|UserRole whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|UserRole whereCreatedAt($value)
 * @method static Builder|UserRole whereDashboardAccess($value)
 * @method static Builder|UserRole whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|UserRole whereId($value)
 * @method static Builder|UserRole whereLike($column, $value, $boolean = 'and')
 * @method static Builder|UserRole whereName($value)
 * @method static Builder|UserRole wherePermissions($value)
 * @method static Builder|UserRole whereType($value)
 * @method static Builder|UserRole whereUpdatedAt($value)
 * @mixin Eloquent
 */
class UserRole extends Model
{
    use HasFactory, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'permissions',
        'dashboard_access',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',
    ];

    /**
     * Get the users for the user role
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * @param $route
     * @return bool
     * @throws Exception
     */
    public function checkPermission($route): bool
    {
        if ($this->id === 1) {
            return true;
        }
        if (!$this->checkDashboardAccess()) {
            return false;
        }
        return in_array($route, json_decode((string) $this->permissions, true, 512, JSON_THROW_ON_ERROR), true);
    }

    /**
     * @return bool
     */
    public function checkDashboardAccess(): bool
    {
        if ($this->id === 1) {
            return true;
        }

        return (bool) $this->dashboard_access;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPermissions(): array
    {
        $controllers = [];
        $rolePermissionsArray = json_decode((string) $this->permissions, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($rolePermissionsArray)) {
            $rolePermissionsArray = []; // Ensure it's an array
        }

        foreach (Route::getRoutes()->getIterator() as $route) {
            // Check if 'controller' key exists and if the URI matches 'api/dashboard'
            if (strpos($route->uri(), 'api/dashboard') !== false && isset($route->getAction()['controller'])) {
                $controllerAction = $route->getAction()['controller'];

                // Process only if the controller action is a string (classname@method)
                if (is_string($controllerAction)) {
                    $controllerClass = explode('@', $controllerAction)[0];
                    // Convert full class namespace to dot notation for permission key
                    $path = str_replace('\\', '.', $controllerClass); 
                    
                    // Grant permission if role is ID 1 (admin) or if the path is in their permissions list
                    $controllers[$path] = ($this->id === 1) || in_array($path, $rolePermissionsArray, true);
                }
            }
        }
        return $controllers;
    }
}
