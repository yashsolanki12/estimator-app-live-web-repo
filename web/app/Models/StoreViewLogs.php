<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreViewLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *a
     * @var array
     */
    protected $fillable = [
        'user_store_id',
        'product_id',
        'product_name',
        'page',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->table = 'timer_view_logs';
    }
}
