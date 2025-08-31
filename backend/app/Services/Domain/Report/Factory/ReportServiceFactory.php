<?php

namespace Evently\Services\Domain\Report\Factory;

use Evently\DomainObjects\Enums\ReportTypes;
use Evently\Services\Domain\Report\AbstractReportService;
use Evently\Services\Domain\Report\Reports\DailySalesReport;
use Evently\Services\Domain\Report\Reports\ProductSalesReport;
use Evently\Services\Domain\Report\Reports\PromoCodesReport;
use Illuminate\Support\Facades\App;

class ReportServiceFactory
{
    public function create(ReportTypes $reportType): AbstractReportService
    {
        return match ($reportType) {
            ReportTypes::PRODUCT_SALES => App::make(ProductSalesReport::class),
            ReportTypes::DAILY_SALES_REPORT => App::make(DailySalesReport::class),
            ReportTypes::PROMO_CODES_REPORT => App::make(PromoCodesReport::class),
        };
    }
}
