<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int|null $id
 * @property string $name
 * @property string $login
 * @property string $password
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static static create(array $attributes = [])
 * @method static static findOrFail(mixed $id, array $columns = ['*'])
 */
class UserModel extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = ['name', 'login', 'password'];

    protected $hidden = ['password'];

    public function balance(): HasOne
    {
        return $this->hasOne(UserBalanceModel::class, 'user_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(OperationModel::class, 'user_id');
    }
}
