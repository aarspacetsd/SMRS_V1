<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceReturnsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id'); // disamakan dengan invoices.id
            $table->unsignedBigInteger('user_id'); // sesuai dengan users.id
            $table->float('return_amount');
            $table->string('return_reason')->nullable();
            $table->boolean('status')->default(1);

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_returns');
    }
}
