<?php

    use App\Enums\VendorStatusEnum;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique()->index();
                $table->string('phone')->unique()->index();
                $table->string('password');
                $table->string('national_id_photo');
                $table->string('status')->default(VendorStatusEnum::PENDING->value);
                $table->timestamp('actioned_at')->nullable();
                $table->string('rejected_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreignId('actioned_by')->nullable()->constrained('admins')->cascadeOnDelete();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('vendors');
        }
    };
