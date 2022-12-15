<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $admin1 = User::factory()->create([
            'name' => 'Admin1 Persona',
            'email' => 'admin1@gmail.com'
        ]);

        $admin2 = User::factory()->create([
            'name' => 'Admin 2 Fulano',
            'email' => 'admin2@gmail.com'
        ]);

        $ana1 = User::factory()->create([
            'name' => 'Analista 1',
            'email' => 'ana1@gmail.com'
        ]);

        $ana2 = User::factory()->create([
            'name' => 'Analista 2',
            'email' => 'ana2@gmail.com'
        ]);

        $inv1 = User::factory()->create([
            'name' => 'Investigador 1',
            'email' => 'inv1@gmail.com'
        ]);

        $inv2 = User::factory()->create([
            'name' => 'Investigador 2',
            'email' => 'inv2@gmail.com'
        ]);

        $super = User::factory()->create([
            'name' => 'Super admin',
            'email' => 'super@gmail.com'
        ]);


        $cliente = Role::create(['description' => 'Cliente']);
        $analista = Role::create(['description' => 'Analista']);
        $investigador = Role::create(['description' => 'Investigador']);
        $super_admin = Role::create(['description' => 'Super_admin']);

        /*Admin 1*/
        RoleUser::create([
            'licenses' => 10,
            'role_id'  => $cliente->id,
            'user_id'  => $admin1->id,
        ]);
        /*Admin 2*/
        RoleUser::create([
            'licenses' => 2,
            'role_id'  => $cliente->id,
            'user_id'  => $admin2->id,
        ]);

        /*Analista 1*/
        RoleUser::create([
            'owner_id' => $admin1->id,
            'role_id'  => $analista->id,
            'user_id'  => $ana1->id,
        ]);

        /*Analista 2*/
        RoleUser::create([
            'owner_id' => $admin2->id,
            'role_id'  => $analista->id,
            'user_id'  => $ana2->id,
        ]);

        /*Investigador 1*/
        RoleUser::create([
            'owner_id' => $admin1->id,
            'role_id'  => $investigador->id,
            'user_id'  => $inv1->id,
        ]);

        /*Investigador 2*/
        RoleUser::create([
            'owner_id' => $admin2->id,
            'role_id'  => $investigador->id,
            'user_id'  => $inv2->id,
        ]);

        /*Super admin*/
        RoleUser::create([
            'role_id'  => $super_admin->id,
            'user_id'  => $super->id,
        ]);


        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
