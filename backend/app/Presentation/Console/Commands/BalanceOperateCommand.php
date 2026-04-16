<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\Balance\Commands\ProcessBalanceCommand;
use App\Application\Balance\Commands\QueueBalanceOperationCommand;
use App\Application\Balance\Handlers\ProcessBalanceHandler;
use App\Application\Balance\Handlers\QueueBalanceOperationHandler;
use App\Application\Balance\Queries\DashboardQueryHandler;
use App\Domain\Balance\Exceptions\InsufficientFundsException;
use App\Domain\Identity\Exceptions\UserNotFoundException;
use Illuminate\Console\Command;

final class BalanceOperateCommand extends Command
{
    protected $signature = 'balance:operate
        {login : User login}
        {type : Operation type: credit or debit}
        {amount : Amount (positive number)}
        {description : Operation description}
        {--queue : Dispatch via Laravel Queue}';

    protected $description = 'Process a balance operation (credit/debit)';

    public function handle(
        ProcessBalanceHandler $syncHandler,
        QueueBalanceOperationHandler $queueHandler,
        DashboardQueryHandler $dashboardQueryHandler,
    ): int {
        $rawLogin = $this->argument('login');
        $rawType = $this->argument('type');
        $rawAmount = $this->argument('amount');
        $rawDescription = $this->argument('description');

        if (! is_string($rawLogin) || ! is_string($rawType) || ! is_string($rawAmount) || ! is_string($rawDescription)) {
            $this->error('Invalid arguments.');

            return self::FAILURE;
        }

        $login = $rawLogin;
        $type = $rawType;
        $amount = (float) $rawAmount;
        $description = $rawDescription;

        if (! in_array($type, ['credit', 'debit'], true)) {
            $this->error("Invalid type \"{$type}\". Use: credit or debit.");

            return self::FAILURE;
        }

        if ($amount <= 0) {
            $this->error('Amount must be greater than 0.');

            return self::FAILURE;
        }

        if ($this->option('queue')) {
            return $this->runQueued($queueHandler, $login, $type, $amount, $description);
        }

        return $this->runSync($syncHandler, $dashboardQueryHandler, $login, $type, $amount, $description);
    }

    private function runSync(
        ProcessBalanceHandler $handler,
        DashboardQueryHandler $dashboardQueryHandler,
        string $login,
        string $type,
        float $amount,
        string $description,
    ): int {
        try {
            $operation = $handler->handle(new ProcessBalanceCommand($login, $type, $amount, $description));
            $data = $dashboardQueryHandler->handle($operation->userId);
            $this->info("Operation completed. New balance: {$data['balance']}");

            return self::SUCCESS;
        } catch (UserNotFoundException|InsufficientFundsException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function runQueued(
        QueueBalanceOperationHandler $handler,
        string $login,
        string $type,
        float $amount,
        string $description,
    ): int {
        try {
            $operation = $handler->handle(new QueueBalanceOperationCommand($login, $type, $amount, $description));
            $this->info("Operation queued (ID: {$operation->id}). Run worker: php artisan queue:work --queue=balance");

            return self::SUCCESS;
        } catch (UserNotFoundException|InsufficientFundsException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
