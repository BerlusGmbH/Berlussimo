<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LaravelPersmissionUpdateV1ToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('person_has_permissions')) {
            $tableNames = config('permission.table_names');
            $columnNames = config('permission.column_names');
            $userModelNamespace = "Person"; // Change this value if you didn't use default User namespace in V1

            // Rename V1 tables
            Schema::rename('permissions', 'permissions_v1');
            Schema::rename('roles', 'roles_v1');
            Schema::rename('role_has_permissions', 'role_has_permissions_v1');
            Schema::rename('person_has_permissions', 'person_has_permissions_v1');
            Schema::rename('person_has_roles', 'person_has_roles_v1');

            // Drop V1 foreign key constraints
            Schema::table('role_has_permissions_v1', function ($table) {
                $table->dropForeign('role_has_permissions_permission_id_foreign');
                $table->dropForeign('role_has_permissions_role_id_foreign');
            });

            Schema::table('person_has_permissions_v1', function ($table) {
                $table->dropForeign('person_has_permissions_permission_id_foreign');
                $table->dropForeign('person_has_permissions_person_id_foreign');
            });

            Schema::table('person_has_roles_v1', function ($table) {
                $table->dropForeign('person_has_roles_person_id_foreign');
                $table->dropForeign('person_has_roles_role_id_foreign');
            });

            // Create V2.28 tables
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });

            Schema::create($tableNames['roles'], function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });

            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type',]);
                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');
                $table->primary(['permission_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            });

            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type',]);
                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');
                $table->primary(['role_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            });

            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedInteger('permission_id');
                $table->unsignedInteger('role_id');
                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');
                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');
                $table->primary(['permission_id', 'role_id']);
                app('cache')->forget('spatie.permission.cache');
            });

            // Migrate V1 tables to V2 tables
            $roles = collect(DB::table('roles_v1')->select()->get())->map(function ($x) {
                return (array)$x + ['guard_name' => config('auth.defaults.guard')];
            })->toArray();
            DB::table($tableNames['roles'])->insert($roles);

            $permissions = collect(DB::table('permissions_v1')->select()->get())->map(function ($x) {
                return (array)$x + ['guard_name' => config('auth.defaults.guard')];
            })->toArray();
            DB::table($tableNames['permissions'])->insert($permissions);

            $model_has_permissions = collect(DB::table('person_has_permissions_v1')->select('person_id AS model_id')->get())->map(function ($x) use ($userModelNamespace) {
                return (array)$x + ['model_type' => $userModelNamespace];
            })->toArray();
            DB::table($tableNames['model_has_permissions'])->insert($model_has_permissions);

            $model_has_roles = collect(DB::table('person_has_roles_v1')->select(['person_id AS model_id', 'role_id'])->get())->map(function ($x) use ($userModelNamespace) {
                return (array)$x + ['model_type' => $userModelNamespace];
            })->toArray();
            DB::table($tableNames['model_has_roles'])->insert($model_has_roles);

            $role_has_permissions = collect(DB::table('role_has_permissions_v1')->select()->get())->map(function ($x) {
                return (array)$x;
            })->toArray();
            DB::table($tableNames['role_has_permissions'])->insert($role_has_permissions);

            // Drop V1 tables
            // Remove this lines if you want to keep the renamed V1 tables
            Schema::drop('role_has_permissions_v1');
            Schema::drop('person_has_permissions_v1');
            Schema::drop('person_has_roles_v1');
            Schema::drop('roles_v1');
            Schema::drop('permissions_v1');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // WARNING: You can't rollback to V1 tables with this script!

        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
}
