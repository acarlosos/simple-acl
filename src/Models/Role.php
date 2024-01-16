<?php

namespace acarlosos\SimpleAcl\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    const ADMIN = 'admin';
    const USER = 'user';

    protected $table      = 'roles';

    protected $fillable = [
        'label',
        'description',
        'sort_index',
    ];

    protected $casts = [
        'id'         => 'integer',
        'sort_index' => 'integer',
    ];

    protected $dates = [ 'deleted_at' ];

    public function permissions()
    {
        return $this->belongsToMany( Permission::class, 'permission_role' )->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(  config( 'simple-acl.user-class' ) , 'role_user' )->wherePivot('organization_id', session()->get('organization_id'))->withTimestamps();
    }

    public function attachPermission( Permission $permission )
    {
        unset( $this->permissions );
        unset( $permission->roles );
        unset( $permission->users );

        $this->permissions()->attach( $permission->id );

        $this->rebuildUsersPermissions();
    }

    public function detachPermission( Permission $permission )
    {
        unset( $this->permissions );
        unset( $permission->roles );
        unset( $permission->users );

        $this->permissions()->detach( $permission->id );

        $this->rebuildUsersPermissions();
    }

    public function attachUser( Model $user )
    {
        unset( $this->users );

        $this->users()->sync( [ $user->id => [
            'organization_id' => session()->get('organization_id') ?? 0
        ]], false );

        $user->rebuildPermissions();
    }

    public function detachUser( Model $user )
    {
        unset( $this->users );

        $this->users()->detach( $user->id );

        $user->rebuildPermissions();
    }

    public function rebuildUsersPermissions()
    {
        $this->users()->get()->each( function ( Model $user ) {
            $user->rebuildPermissions();
        } );
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
