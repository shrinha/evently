<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AttendeeCheckInDomainObject;
use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Models\Attendee;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendeeRepository extends BaseRepository implements AttendeeRepositoryInterface
{
    protected function getModel(): string
    {
        return Attendee::class;
    }

    public function getDomainObject(): string
    {
        return AttendeeDomainObject::class;
    }

    public function findByEventIdForExport(int $eventId): Collection
    {
        $this->applyConditions([
            'attendees.event_id' => $eventId,
        ]);

        $this->model->select('attendees.*');
        $this->model->join('orders', 'orders.id', '=', 'attendees.order_id');
        $this->model->whereIn('orders.status', [
            OrderStatus::AWAITING_OFFLINE_PAYMENT->name,
            OrderStatus::COMPLETED->name,
            OrderStatus::CANCELLED->name
        ]);

        $model = $this->model->limit(10000)->get();
        $this->resetModel();

        return $this->handleResults($model);
    }


    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            ['attendees.event_id', '=', $eventId]
        ];

        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(
                        DB::raw(
                            sprintf(
                                "(%s||' '||%s)",
                                'attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME,
                                'attendees.' . AttendeeDomainObjectAbstract::LAST_NAME,
                            )
                        ), 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::LAST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::PUBLIC_ID, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->select('attendees.*')
            ->join('orders', 'orders.id', '=', 'attendees.order_id')
            ->whereIn('orders.status', [OrderStatus::COMPLETED->name, OrderStatus::CANCELLED->name, OrderStatus::AWAITING_OFFLINE_PAYMENT->name])
            ->orderBy(
                'attendees.' . ($params->sort_by ?? AttendeeDomainObject::getDefaultSort()),
                $params->sort_direction ?? 'desc',
            );

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }

    public function getAttendeesByCheckInShortId(string $shortId, QueryParamsDTO $params): Paginator
    {
        $where = [];
        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(
                        DB::raw(
                            sprintf(
                                "(%s||' '||%s)",
                                'attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME,
                                'attendees.' . AttendeeDomainObjectAbstract::LAST_NAME,
                            )
                        ), 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::LAST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::FIRST_NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::PUBLIC_ID, 'ilike', '%' . $params->query . '%')
                    ->orWhere('attendees.' . AttendeeDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->select('attendees.*')
            ->join('orders', 'orders.id', '=', 'attendees.order_id')
            ->join('product_check_in_lists', 'product_check_in_lists.product_id', '=', 'attendees.product_id')
            ->join('check_in_lists', 'check_in_lists.id', '=', 'product_check_in_lists.check_in_list_id')
            ->where('check_in_lists.short_id', $shortId)
            ->whereIn('attendees.status',[AttendeeStatus::ACTIVE->name, AttendeeStatus::CANCELLED->name, AttendeeStatus::AWAITING_PAYMENT->name])
            ->whereIn('orders.status', [OrderStatus::COMPLETED->name, OrderStatus::AWAITING_OFFLINE_PAYMENT->name]);

        $this->loadRelation(new Relationship(AttendeeCheckInDomainObject::class, name: 'check_in'));

        return $this->simplePaginateWhere(
            where: $where,
            limit: min($params->per_page, 250),
        );
    }
}
