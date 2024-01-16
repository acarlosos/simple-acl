<?php

namespace acarlosos\SimpleAcl\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    protected $table      = 'permissions';

    protected $fillable = [
        'label',
        'description',
        'sort_index',
        'module',
        'action',
    ];

    protected $casts = [
        'id'         => 'integer',
        'sort_index' => 'integer',
    ];

    protected $dates = [ 'deleted_at' ];

    public function roles()
    {
        return $this->belongsToMany( Role::class, 'permission_role' )->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(   config( 'simple-acl.user-class' ) , 'permission_user' )->wherePivot('organization_id', session()->get('organization_id'))->withTimestamps();
    }

    public function scopeHasLabel( Builder $builder, $label )
    {
        $builder->where( 'label', $label );

        return $builder;
    }

    public function scopeOrdered( Builder $builder )
    {
        $builder->orderBy( 'sort_index' )->orderBy( 'description' );

        return $builder;
    }
}
