<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Sale;
use App\Models\User;
use App\Services\GoldCollateralService;
use App\Services\InstallmentCalculatorService;
use App\Support\VehicleOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;

class AutomayaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->wipeOperationalData();
        $this->call(VehicleCatalogSeeder::class);

        $userId = User::query()->value('id')
            ?? User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@automaya.test',
            ])->id;

        $contacts = $this->seedContacts($userId);
        $sellers = $contacts['sellers'];
        $buyers = $contacts['buyers'];
        $colleagues = $contacts['colleagues'];

        $catalog = $this->vehicleCatalog();
        $usdRate = 1_120_000;
        $goldRate = 8_400_000;

        if (Schema::hasTable('gold_rates')) {
            DB::table('gold_rates')->updateOrInsert(
                ['item' => '18ayar', 'rate_date' => now()->toDateString()],
                [
                    'rate_per_gram' => $goldRate,
                    'source' => 'demo',
                    'fetched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($catalog as $index => $car) {
            $this->createStockedCar($car, $index, $sellers[$index % count($sellers)], $userId, $usdRate);
        }

        foreach ($catalog as $index => $car) {
            $this->createAnnouncedCar($car, $index, $colleagues[$index % count($colleagues)], $userId);
        }

        foreach ($catalog as $index => $car) {
            $device = $this->createStockedCar($car, $index + 40, $sellers[$index % count($sellers)], $userId, $usdRate, sold: true);
            $this->createCashSale($device, $car, $index, $buyers[$index % count($buyers)], $userId, $usdRate);
        }

        $calculator = app(InstallmentCalculatorService::class);
        $goldService = app(GoldCollateralService::class);

        foreach ($catalog as $index => $car) {
            $device = $this->createStockedCar($car, $index + 80, $sellers[$index % count($sellers)], $userId, $usdRate, sold: true);
            $this->createInstallmentSale(
                $device,
                $car,
                $index,
                $buyers[$index % count($buyers)],
                $userId,
                $usdRate,
                $goldRate,
                $calculator,
                $goldService
            );
        }

        $this->seedWantedRequests($catalog, $colleagues);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function vehicleCatalog(): array
    {
        $colors = ['سفید', 'مشکی', 'نقره‌ای', 'خاکستری', 'آبی', 'قرمز', 'بژ'];
        $bodies = array_keys(VehicleOptions::bodyConditions());

        $rows = [
            ['brand' => 'ایران‌خودرو', 'model' => 'دنا پلاس', 'photo' => 'dena-plus.jpg', 'market' => 2_740_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'ایران‌خودرو', 'model' => 'تارا', 'photo' => 'tara.jpg', 'market' => 1_955_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'سایپا', 'model' => 'شاهین', 'photo' => 'shahin.jpg', 'market' => 2_270_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'سایپا', 'model' => 'اطلس', 'photo' => 'atlas.jpg', 'market' => 1_530_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'مدیران خودرو', 'model' => 'تیگو 7 پرو', 'photo' => 'tiggo-7.jpg', 'market' => 6_390_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'مدیران خودرو', 'model' => 'آریزو 6', 'photo' => 'arizo.jpg', 'market' => 5_050_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'بهمن موتور', 'model' => 'فیدلیتی', 'photo' => 'fidelity.jpg', 'market' => 5_230_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'بهمن موتور', 'model' => 'دیگنیتی', 'photo' => 'dignity.jpg', 'market' => 3_800_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'کرمان موتور', 'model' => 'KMC J7', 'photo' => 'kmc-j7.jpg', 'market' => 4_400_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'پارس‌خودرو', 'model' => 'تندر 90', 'photo' => 'tondar.jpg', 'market' => 980_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'ایران‌خودرو', 'model' => 'پژو 207', 'photo' => 'peugeot-207.jpg', 'market' => 1_800_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'سایپا', 'model' => 'کوییک S', 'photo' => 'quick.jpg', 'market' => 1_195_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'سایپا', 'model' => 'ساینا S', 'photo' => 'saina.jpg', 'market' => 1_270_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'ایران‌خودرو', 'model' => 'ری‌را', 'photo' => 'rira.jpg', 'market' => 3_650_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
            ['brand' => 'مدیران خودرو', 'model' => 'X22', 'photo' => 'x22.jpg', 'market' => 1_820_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'کرمان موتور', 'model' => 'JAC J4', 'photo' => 'j4.jpg', 'market' => 2_410_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'ایران‌خودرو', 'model' => 'پژو پارس', 'photo' => 'pars.jpg', 'market' => 2_180_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'ایران‌خودرو', 'model' => 'سمند EF7', 'photo' => 'samand.jpg', 'market' => 1_450_000_000, 'transmission' => 'manual', 'fuel_type' => 'petrol'],
            ['brand' => 'زامیاد', 'model' => 'زامیاد Z24', 'photo' => 'zamyad.jpg', 'market' => 1_840_000_000, 'transmission' => 'manual', 'fuel_type' => 'dual_fuel'],
            ['brand' => 'مدیران خودرو', 'model' => 'آریزو 5', 'photo' => 'arizo.jpg', 'market' => 3_700_000_000, 'transmission' => 'automatic', 'fuel_type' => 'petrol'],
        ];

        foreach ($rows as $index => &$row) {
            $row['color'] = $colors[$index % count($colors)];
            $row['body_condition'] = $bodies[$index % count($bodies)];
        }

        return $rows;
    }

    private function createStockedCar(
        array $car,
        int $index,
        int $sellerId,
        int $userId,
        int $usdRate,
        bool $sold = false
    ): Device {
        $year = 1405 - ($index % 5);
        $mileage = 4_000 + ($index * 3_700);
        $price = $this->adjustedPrice($car['market'], $year, $mileage);
        $purchasePrice = (int) round($price * 0.91);

        $device = Device::create([
            'brand' => $car['brand'],
            'model' => $car['model'],
            'model_year' => $year,
            'mileage' => $mileage,
            'color' => $car['color'],
            'transmission' => $car['transmission'],
            'fuel_type' => $car['fuel_type'],
            'insurance_months' => 3 + ($index % 10),
            'body_condition' => $car['body_condition'],
            'vin' => sprintf('IR%02d%02d%09d', $year % 100, $index % 97, 100000000 + $index),
            'description' => $car['brand'].' '.$car['model'].'، قیمت هم‌تراز آگهی‌های باما.',
            'status' => $sold ? 'sold' : 'in_stock',
            'created_by' => $userId,
        ]);

        DB::table('purchases')->insert([
            'device_id' => $device->id,
            'seller_id' => $sellerId,
            'purchase_price' => $purchasePrice,
            'purchase_date' => now()->subDays(20 + $index)->toDateString(),
            'usd_rate' => $usdRate,
            'usd_rate_date' => now()->subDays(20 + $index)->toDateString(),
            'usd_rate_source' => 'demo',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->attachPhoto($device->id, $car['photo']);

        return $device;
    }

    private function createAnnouncedCar(array $car, int $index, int $colleagueId, int $userId): void
    {
        $year = 1404 - ($index % 4);
        $mileage = 8_000 + ($index * 2_900);
        $price = $this->adjustedPrice($car['market'], $year, $mileage);

        $device = Device::create([
            'brand' => $car['brand'],
            'model' => $car['model'],
            'model_year' => $year,
            'mileage' => $mileage,
            'color' => $car['color'],
            'transmission' => $car['transmission'],
            'fuel_type' => $car['fuel_type'],
            'insurance_months' => 2 + ($index % 8),
            'body_condition' => $car['body_condition'],
            'vin' => null,
            'status' => 'announced',
            'announced_by_id' => $colleagueId,
            'announced_price' => (int) round($price * 1.03),
            'announced_at' => now()->subDays($index + 1)->toDateString(),
            'created_by' => $userId,
        ]);

        $this->attachPhoto($device->id, $car['photo']);
    }

    private function createCashSale(
        Device $device,
        array $car,
        int $index,
        int $buyerId,
        int $userId,
        int $usdRate
    ): void {
        $saleDate = now()->subDays(8 + $index)->toDateString();
        $price = $this->adjustedPrice($car['market'], (int) $device->model_year, (int) $device->mileage);

        $sale = new Sale();
        $sale->device_id = $device->id;
        $sale->buyer_id = $buyerId;
        $sale->sale_type = 'cash';
        $sale->guarantee_type = null;
        $sale->sale_price = $price;
        $sale->down_payment = $price;
        $sale->contract_total = $price;
        $sale->sale_date = $saleDate;
        $sale->usd_rate = $usdRate;
        $sale->usd_rate_date = $saleDate;
        $sale->usd_rate_source = 'demo';
        $sale->created_by = $userId;
        $sale->save();
    }

    private function createInstallmentSale(
        Device $device,
        array $car,
        int $index,
        int $buyerId,
        int $userId,
        int $usdRate,
        int $goldRate,
        InstallmentCalculatorService $calculator,
        GoldCollateralService $goldService
    ): void {
        $saleDate = now()->subDays(30 + $index)->toDateString();
        $firstDue = Jalalian::fromCarbon(now()->subDays(30 + $index))
            ->addMonths(1)
            ->toCarbon()
            ->toDateString();
        $price = $this->adjustedPrice($car['market'], (int) $device->model_year, (int) $device->mileage);
        $down = (int) round($price * 0.30);
        $count = 6 + ($index % 7);
        $useGold = $index % 5 === 0;

        $calc = $calculator->calculate(
            salePrice: $price,
            downPayment: $down,
            monthlyProfitRate: 6.5,
            installmentCount: $count,
            saleDate: $saleDate,
            firstDueDate: $firstDue,
        );

        $sale = new Sale();
        $sale->device_id = $device->id;
        $sale->buyer_id = $buyerId;
        $sale->sale_type = 'installment';
        $sale->guarantee_type = $useGold ? 'gold' : 'check';
        $sale->sale_price = $price;
        $sale->down_payment = $down;
        $sale->monthly_profit_rate = $calc['monthly_profit_rate'];
        $sale->installment_profit = $calc['installment_profit'];
        $sale->contract_total = $calc['contract_total'];
        $sale->standard_first_due_date = $calc['standard_first_due_date'];
        $sale->first_due_date = $calc['first_due_date'];
        $sale->deferment_months = $calc['deferment_months'];
        $sale->deferment_days = $calc['deferment_days'];
        $sale->deferment_profit = $calc['deferment_profit'];
        $sale->sale_date = $saleDate;
        $sale->usd_rate = $usdRate;
        $sale->usd_rate_date = $saleDate;
        $sale->usd_rate_source = 'demo';
        $sale->created_by = $userId;
        $sale->save();

        $banks = ['بانک ملت', 'بانک ملی ایران', 'بانک صادرات ایران', 'بانک پاسارگاد', 'بانک سامان'];

        foreach ($calc['installments'] as $row) {
            $paid = $row['installment_number'] <= 2;

            DB::table('installments')->insert([
                'sale_id' => $sale->id,
                'installment_number' => $row['installment_number'],
                'due_date' => $row['due_date'],
                'amount' => $row['amount'],
                'paid_amount' => $paid ? $row['amount'] : 0,
                'status' => $paid ? 'paid' : 'pending',
                'paid_at' => $paid ? $row['due_date'] : null,
                'check_number' => $useGold ? null : (string) (1200000 + $sale->id * 20 + $row['installment_number']),
                'bank_name' => $useGold ? null : $banks[$index % count($banks)],
                'sayad_id' => $useGold ? null : substr((string) (8000000000000000 + (($sale->id * 17 + $row['installment_number']) % 1000000000000000)), 0, 16),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($useGold) {
            $collateral = $goldService->calculate(
                salePrice: $price,
                downPayment: $down,
                monthlyProfitRate: 6.5,
                goldRatePerGram: $goldRate,
            );

            DB::table('sale_gold_collaterals')->insert([
                'sale_id' => $sale->id,
                'base_principal' => $collateral['base_principal'],
                'coverage_months' => $collateral['coverage_months'],
                'monthly_profit_rate' => $collateral['monthly_profit_rate'],
                'coverage_profit' => $collateral['coverage_profit'],
                'coverage_amount' => $collateral['coverage_amount'],
                'gold_rate_item' => '18ayar',
                'gold_rate_per_gram' => $goldRate,
                'gold_rate_date' => $saleDate,
                'gold_rate_source' => 'demo',
                'gold_karat' => 18,
                'required_weight' => $collateral['required_weight'],
                'received_weight' => round($collateral['required_weight'] + 2, 4),
                'gold_type' => 'سکه و شمش',
                'description' => 'ضمانت طلای نمایشگاهی',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedWantedRequests(array $catalog, array $colleagues): void
    {
        if (! Schema::hasTable('wanted_device_requests')) {
            return;
        }

        $names = DB::table('contacts')->whereIn('id', $colleagues)->pluck('name', 'id');
        $mobiles = DB::table('contacts')->whereIn('id', $colleagues)->pluck('mobile', 'id');

        foreach ($catalog as $index => $car) {
            $colleagueId = $colleagues[$index % count($colleagues)];
            $price = (int) round($car['market'] * 0.92);

            $row = [
                'requester_name' => $names[$colleagueId] ?? 'همکار نمایشگاه',
                'requester_mobile' => $mobiles[$colleagueId] ?? '09120000000',
                'brand' => $car['brand'],
                'model' => $car['model'],
                'storage' => (string) (1402 + ($index % 4)),
                'color' => $car['color'],
                'condition_grade' => $car['body_condition'],
                'max_price' => $price,
                'description' => 'درخواست خرید '.$car['model'].' با قیمت نزدیک به بازار باما.',
                'created_at' => now()->subHours($index + 1),
                'updated_at' => now()->subHours($index + 1),
            ];

            if (Schema::hasColumn('wanted_device_requests', 'origin')) {
                $row['origin'] = 'organic';
            }

            DB::table('wanted_device_requests')->insert($row);
        }
    }

    private function attachPhoto(int $deviceId, string $filename): void
    {
        if (! Schema::hasTable('device_images')) {
            return;
        }

        DB::table('device_images')->insert([
            'device_id' => $deviceId,
            'image_path' => '/images/vehicles/'.$filename,
            'is_cover' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function adjustedPrice(int $market, int $year, int $mileage): int
    {
        $age = max(0, 1405 - $year);
        $factor = 1 - ($age * 0.045) - min(0.18, $mileage / 220_000);

        return (int) (round(($market * max(0.62, $factor)) / 1_000_000) * 1_000_000);
    }

    /**
     * @return array{sellers: array<int, int>, buyers: array<int, int>, colleagues: array<int, int>}
     */
    private function seedContacts(int $userId): array
    {
        $sellers = [
            'علی رضایی' => '09121230001',
            'محمد کریمی' => '09121230002',
            'حسین موسوی' => '09121230003',
            'رضا نوری' => '09121230004',
            'امیر حیدری' => '09121230005',
            'سعید جعفری' => '09121230006',
            'مهدی صادقی' => '09121230007',
            'پرویز احمدی' => '09121230008',
        ];

        $buyers = [
            'نگار محمدی' => '09123001001',
            'سارا کاظمی' => '09123001002',
            'نیما اکبری' => '09123001003',
            'کیانوش مرادی' => '09123001004',
            'هانیه شریفی' => '09123001005',
            'فرهاد نعمتی' => '09123001006',
            'آرش سلیمانی' => '09123001007',
            'مینا رستمی' => '09123001008',
            'پویا فلاح' => '09123001009',
            'الهام یوسفی' => '09123001010',
            'بهرام قربانی' => '09123001011',
            'شادی ناصری' => '09123001012',
        ];

        $colleagues = [
            'همکار — اتوگالری آفتاب' => '09135550001',
            'همکار — نمایشگاه پارس' => '09135550002',
            'همکار — اتوخسروانی' => '09135550003',
            'همکار — کرمان موتور رسالت' => '09135550004',
            'همکار — مدیران خودرو ونک' => '09135550005',
            'همکار — بهمن موتور ستارخان' => '09135550006',
            'همکار — ایران‌خودرو جردن' => '09135550007',
            'همکار — سایپا شهرری' => '09135550008',
        ];

        $map = ['sellers' => [], 'buyers' => [], 'colleagues' => []];

        foreach (['sellers' => $sellers, 'buyers' => $buyers, 'colleagues' => $colleagues] as $group => $people) {
            $type = $group === 'colleagues' ? 'colleague' : 'individual';

            foreach ($people as $name => $mobile) {
                $map[$group][] = (int) DB::table('contacts')->insertGetId([
                    'name' => $name,
                    'mobile' => $mobile,
                    'contact_type' => $type,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $map;
    }

    private function wipeOperationalData(): void
    {
        DB::table('entity_notes')->delete();

        if (Schema::hasTable('installment_images')) {
            DB::table('installment_images')->delete();
        }

        if (Schema::hasTable('installments')) {
            DB::table('installments')->delete();
        }

        if (Schema::hasTable('sale_gold_collaterals')) {
            DB::table('sale_gold_collaterals')->delete();
        }

        if (Schema::hasTable('sales')) {
            DB::table('sales')->delete();
        }

        if (Schema::hasTable('purchases')) {
            DB::table('purchases')->delete();
        }

        if (Schema::hasTable('device_images')) {
            DB::table('device_images')->delete();
        }

        if (Schema::hasTable('wanted_device_requests')) {
            DB::table('wanted_device_requests')->delete();
        }

        DB::table('devices')->delete();
        DB::table('contacts')->delete();
    }
}
