<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ext_egg_changer_groups', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('ext_egg_changer_group_eggs', function (Blueprint $table): void {
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('egg_id');

            $table->primary(['group_id', 'egg_id']);
            $table->foreign('group_id')->references('id')->on('ext_egg_changer_groups')->cascadeOnDelete();
            $table->foreign('egg_id')->references('id')->on('eggs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_egg_changer_group_eggs');
        Schema::dropIfExists('ext_egg_changer_groups');
    }
};
