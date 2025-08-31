<?php

namespace Evently\Http\Actions\ProductCategories;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\CannotDeleteEntityException;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\ProductCategory\DeleteProductCategoryHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class DeleteProductCategoryAction extends BaseAction
{
    public function __construct(
        private readonly DeleteProductCategoryHandler $deleteProductCategoryHandler,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws CannotDeleteEntityException
     */
    public function __invoke(
        int $eventId,
        int $productCategoryId,
    ): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->deleteProductCategoryHandler->handle(
                productCategoryId: $productCategoryId,
                eventId: $eventId,
            );
        } catch (CannotDeleteEntityException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_CONFLICT,
            );
        }

        return $this->deletedResponse();
    }
}
