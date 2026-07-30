<?php

namespace App\Providers;

use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxReader;
use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxWriter;
use App\Infrastructure\IdentityAccessEventOutbox\PostgreSql\PostgreSqlIdentityAccessOutbox;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessEventOutboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlIdentityAccessOutbox::class);
        $this->app->alias(PostgreSqlIdentityAccessOutbox::class, IdentityAccessOutboxWriter::class);
        $this->app->alias(PostgreSqlIdentityAccessOutbox::class, IdentityAccessOutboxReader::class);
    }
}
