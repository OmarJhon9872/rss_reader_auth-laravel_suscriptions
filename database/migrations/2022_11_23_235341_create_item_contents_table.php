<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->nullable()->constrained();

            $table->text('field', 100)->nullable();
            $table->longText('value')->nullable();
            $table->text('name', 100)->nullable();
            $table->boolean('showField')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_contents');
    }
};
