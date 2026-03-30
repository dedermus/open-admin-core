<?php

namespace OpenAdminCore\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearPasswordResetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:clear-resets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired password reset tokens';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expire = config('admin.auth.password_reset.expire', 60);
        $expiredAt = now()->subMinutes($expire);

        $deleted = DB::table('admin_password_reset_tokens')
            ->where('created_at', '<', $expiredAt)
            ->delete();

        $this->info("Deleted {$deleted} expired password reset tokens.");

        if ($deleted > 0) {
            Log::channel('password_reset')->info('Expired tokens cleared', [
                'deleted_count' => $deleted,
                'expired_before' => $expiredAt->toDateTimeString(),
            ]);
        }
    }
}
