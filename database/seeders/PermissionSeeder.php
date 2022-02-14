<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'create post']);
        Permission::create(['name' => 'edit post']);
        Permission::create(['name' => 'delete post']);

        Permission::create(['name' => 'create comment']);
        Permission::create(['name' => 'edit comment']);
        Permission::create(['name' => 'delete comment']);

        $writer = Role::create(['name' => 'Writer']);
        $writer->givePermissionTo('create post');
        $writer->givePermissionTo('edit post');
        $writer->givePermissionTo('delete post');

        $member = Role::create(['name' => 'Wember']);
        $member->givePermissionTo('create post');
        $member->givePermissionTo('edit post');
        $member->givePermissionTo('delete post');
    }
}
