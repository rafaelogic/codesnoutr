<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\DB;

class ExampleModel extends Model implements Arrayable
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $table = 'example_models';

    /**
     * The primary key associated with the table.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'metadata'
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     * Enhanced scanner recognizes this as a legitimate Eloquent property.
     */
    protected $dates = [
        'deleted_at',
        'published_at'
    ];

    /**
     * Implementation of Arrayable interface.
     * Enhanced scanner validates interface implementations.
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Example relationship - Enhanced scanner understands Eloquent relationships
     */
    public function posts()
    {
        return $this->hasMany(related: 'App\Models\Post');
    }

    /**
     * Complex query example - Enhanced scanner should allow legitimate raw SQL for complex operations
     */
    public function getAnalyticsData()
    {
        // This is a legitimate use of raw SQL for complex aggregation - should NOT be flagged
        return DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                AVG(rating) as average_rating
            FROM example_models 
            WHERE created_at >= ? 
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", [now()->subDays(30)]);
    }
}
