<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $type
 * @property string $amount
 * @property string $description
 * @property string $status
 * @property Carbon|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static static|null find(mixed $id, array $columns = ['*'])
 * @method static static create(array $attributes = [])
 */
class OperationModel extends Model
{
    protected $table = 'operations';

    protected $fillable = ['user_id', 'type', 'amount', 'description', 'status'];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
