<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\Identity\Commands\CreateUserCommand as CreateUserDTO;
use App\Application\Identity\Handlers\CreateUserHandler;
use App\Domain\Identity\Exceptions\UserAlreadyExistsException;
use Illuminate\Console\Command;

final class CreateUserCommand extends Command
{
    protected $signature = 'user:create {name} {login} {password}';

    protected $description = 'Create a new user';

    public function handle(CreateUserHandler $handler): int
    {
        try {
            $rawName = $this->argument('name');
            $rawLogin = $this->argument('login');
            $rawPassword = $this->argument('password');

            if (! is_string($rawName) || ! is_string($rawLogin) || ! is_string($rawPassword)) {
                $this->error('Invalid arguments.');

                return self::FAILURE;
            }

            $user = $handler->handle(new CreateUserDTO($rawName, $rawLogin, $rawPassword));

            $this->info("User \"{$user->name}\" (login: {$user->login}) created successfully.");

            return self::SUCCESS;
        } catch (UserAlreadyExistsException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
