<?php

namespace Evently\Services\Application\Handlers\User;

use Evently\DomainObjects\UserDomainObject;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Services\Application\Handlers\User\DTO\CancelEmailChangeDTO;
use Psr\Log\LoggerInterface;

class CancelEmailChangeHandler
{
    private LoggerInterface $logger;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        LoggerInterface         $logger,
        UserRepositoryInterface $userRepository,
    )
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    public function handle(CancelEmailChangeDTO $data): UserDomainObject
    {
        $this->userRepository->updateWhere(
            attributes: [
                'pending_email' => null,
            ],
            where: [
                'id' => $data->userId,
            ]
        );

        $this->logger->info('Cancelled email change', [
            'user_id' => $data->userId,
            'account_id' => $data->accountId,
        ]);

        return $this->userRepository->findByIdAndAccountId($data->userId, $data->accountId);
    }
}
