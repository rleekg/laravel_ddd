<?php

declare(strict_types=1);

namespace App\Application\Identity\Handlers;

use App\Application\Identity\Commands\CreateUserCommand;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Repositories\UserRepositoryInterface;
use App\Domain\Identity\Services\UserDomainService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private BalanceRepositoryInterface $balanceRepo,
        private UserDomainService $domainService,
    ) {}

    public function handle(CreateUserCommand $cmd): User
    {
        $this->domainService->ensureLoginUnique($cmd->login, $this->userRepo);

        /** @var User $result */
        $result = DB::transaction(function () use ($cmd): User {
            $saved = $this->userRepo->save(new User(
                null,
                $cmd->name,
                $cmd->login,
                Hash::make($cmd->password),
            ));
            $this->balanceRepo->createForUser((int) $saved->id);

            return $saved;
        });

        return $result;
    }
}
