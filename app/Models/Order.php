<?php

namespace App\Models;

use App\Models\OrderItem;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class Order
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 * @property string $role
 *
 * @property-read User $user
 * @property OrderItem $orderItems
 * @method static Order find(int $id)
 * @method static Order create(array $attributes)
 * @method static Order where(string $column, string $operator = '=', mixed $value)
 */
class Order extends Model
{
    use SoftDeletes, HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['user_id'];

    /**
     * The relations with other models
     *
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            OrderItem::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }

    /**
     * Some Mailisearch customizations to allow search in fields
     *
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('products');
    }

    public function toSearchableArray()
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at
        ];
    }
}
