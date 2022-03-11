<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacanciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('website_id')->unsigned();
            $table->string('job_id')->nullable();
            $table->string('location')->nullable();
            $table->string('job_title')->nullable();
            $table->string('city')->nullable();
            $table->string('job_type')->nullable(); /* Full time */
            $table->string('contract_type')->nullable();
            $table->string('job_category')->nullable();
            $table->string('job_level')->nullable();
            $table->text('job_description')->nullable();
            $table->string('job_url')->nullable();
            $table->text('qualification')->nullable();
            $table->string('opening_date')->nullable();
            $table->string('deadline')->nullable();
            $table->text('about_us')->nullable();
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('websites')
                ->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacancies');
    }
}
