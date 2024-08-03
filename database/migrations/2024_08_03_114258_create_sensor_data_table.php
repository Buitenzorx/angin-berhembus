<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorDataTable extends Migration
{
    public function up()
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->float('gas_level')->default(0);
            $table->float('voltage')->default(0);
            $table->float('current')->default(0);
            $table->float('power')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_data');
    }
}

