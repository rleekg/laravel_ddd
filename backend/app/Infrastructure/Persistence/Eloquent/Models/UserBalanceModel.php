<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $amount
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static static create(array $attributes = [])
 */
class UserBalanceModel extends Model
{
    public $timestamps = false;

    protected $table = 'user_balances';

    protected $fillable = ['user_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
