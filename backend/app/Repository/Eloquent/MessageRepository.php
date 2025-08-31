<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\Generated\MessageDomainObjectAbstract;
use Evently\DomainObjects\MessageDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Models\Message;
use Evently\Repository\Interfaces\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    protected function getModel(): string
    {
        return Message::class;
    }

    public function getDomainObject(): string
    {
        return MessageDomainObject::class;
    }

    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            [MessageDomainObjectAbstract::EVENT_ID, '=', $eventId]
        ];

        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(MessageDomainObjectAbstract::SUBJECT, 'ilike', '%' . $params->query . '%')
                    ->orWhere(MessageDomainObjectAbstract::MESSAGE, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->orderBy(
            $params->sort_by ?? MessageDomainObject::getDefaultSort(),
            $params->sort_direction ?? 'desc',
        );

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }

}
