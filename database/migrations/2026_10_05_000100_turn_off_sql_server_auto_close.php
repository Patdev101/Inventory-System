<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ALTER DATABASE cannot run inside a transaction.
    public $withinTransaction = false;

    /**
     * SQL Server Express creates databases with AUTO_CLOSE on: the database
     * shuts down whenever nobody is connected and takes seconds to reopen
     * on the next page load. Turning it off keeps it ready.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        try {
            DB::unprepared('ALTER DATABASE CURRENT SET AUTO_CLOSE OFF');
        } catch (Throwable $e) {
            // The database login may not be allowed to change this; the
            // system still works, just slower after idle periods.
            Log::warning('Could not turn off AUTO_CLOSE: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        //
    }
};
