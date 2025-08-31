<?php

declare(strict_types=1);

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AffiliateDomainObject;
use Evently\DomainObjects\Generated\AffiliateDomainObjectAbstract;
use Evently\DomainObjects\Status\AffiliateStatus;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Models\Affiliate;
use Evently\Repository\Interfaces\AffiliateRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class AffiliateRepository extends BaseRepository implements AffiliateRepositoryInterface
{
    protected function getModel(): string
    {
        return Affiliate::class;
    }

    public function getDomainObject(): string
    {
        return AffiliateDomainObject::class;
    }

    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            [AffiliateDomainObjectAbstract::EVENT_ID, '=', $eventId]
        ];

        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->orWhere(AffiliateDomainObjectAbstract::NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere(AffiliateDomainObjectAbstract::CODE, 'ilike', '%' . $params->query . '%')
                    ->orWhere(AffiliateDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->orderBy(
            column: $params->sort_by ?? AffiliateDomainObject::getDefaultSort(),
            direction: $params->sort_direction ?? 'desc',
        );

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }

    public function findByCodeAndEventId(string $code, int $eventId): ?AffiliateDomainObject
    {
        return $this->findFirstWhere([
            AffiliateDomainObjectAbstract::CODE => $code,
            AffiliateDomainObjectAbstract::EVENT_ID => $eventId,
            AffiliateDomainObjectAbstract::STATUS => AffiliateStatus::ACTIVE->value,
        ]);
    }

    public function incrementSales(int $affiliateId, float $amount): void
    {
        $this->model->where('id', $affiliateId)
            ->increment('total_sales', 1, [
                'total_sales_gross' => $this->db->raw('total_sales_gross + ' . $amount)
            ]);
    }
}
