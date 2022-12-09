<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /*Definimos las politicas para cada tipo de usuario
        pero el super admin podra realizar cualquier accion*/


        Gate::define('es_super_admin', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 4;
        });
        Gate::define('es_solo_cliente', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 1;
        });
        Gate::define('es_cliente', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 1 or $rol_id == 4;
        });
        Gate::define('es_solo_analista', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 2;
        });
        Gate::define('es_analista', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 2 or $rol_id == 4;
        });
        Gate::define('es_cliente_o_analista', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 1 or $rol_id == 2 or $rol_id == 4;
        });
        Gate::define('es_investigador', function(User $user){
            $rol_id = $user->role->desc_role->id;
            return $rol_id == 3 or $rol_id == 4;
        });

    }
}
