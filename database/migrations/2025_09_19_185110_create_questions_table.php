<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', [
                'multiple_choice',
                'single_choice',
                'text',
                'textarea',
                'rating',
                'yes_no'
            ]);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('help_text')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
