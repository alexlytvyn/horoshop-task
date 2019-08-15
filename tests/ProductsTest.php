<?php

namespace Tests;

use Horoshop\Exceptions\UnavailablePageException;
use Horoshop\ProductAggregator;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    /**
     * @var ProductAggregator
     */
    private $productAggregator;

    public function setUp(): void
    {
        $this->productAggregator = new ProductAggregator('data.json');
    }

    public function testFindByUAH(): void
    {
        $this->assertByCurrency('UAH');
    }

    public function testFindByUSD(): void
    {
        $this->assertByCurrency('USD');
    }

    public function testFindByEUR(): void
    {
        $this->assertByCurrency('EUR');
    }

    public function testFindByRUB(): void
    {
        $this->assertByCurrency('RUB');
    }

    public function testFindPagination_HundredItems(): void
    {
        $result = $this->productAggregator->find('UAH', 1, 100);
        $result = json_decode($result, true);

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['pages']);
        $this->assertEquals(100, $result['perPage']);
        $this->assertCount(100, $result['items']);
    }

    public function testFindPagination_SecondPage(): void
    {
        $result = $this->productAggregator->find('UAH', 2, 40);
        $result = json_decode($result, true);

        $this->assertEquals(2, $result['page']);
        $this->assertEquals(25, $result['pages']);
        $this->assertEquals(40, $result['perPage']);
        $this->assertCount(40, $result['items']);

        $firstElem = [
            'id' => '7b17545b-93bd-4eed-abfa-6fa1da681873',
            'title' => '3000GT',
            'category' => [
                'id' => '5f5147ec-06ee-439a-b0cf-735b4b3bf78a',
                'title' => 'Mazda'
            ],
            'price' => [
                'amount' => 513739.61,
                'discounted_price' => 128434.91,
                'currency' => 'UAH',
                'discount' => [
                    'type' => 'percent',
                    'value' => 75,
                    'relation' => 'product'
                ]
            ]
        ];

        $this->assertEqualsCanonicalizing($firstElem, $result['items'][0]);
    }

    public function testFindPagination_UnavailablePage(): void
    {
        $this->expectException(UnavailablePageException::class);

        $this->productAggregator->find('UAH', 100, 20);
    }

    /**
     * @param string $currency
     */
    private function assertByCurrency(string $currency): void
    {
        $result = $this->findByCurrency($currency);
        $products = $result['items'];
        $prices = array_column($products, 'price');

        $expectedProducts = $this->firstTenResult();
        $expectedPrices = array_column($expectedProducts, 'price');

        $this->assertCount(10, $products);
        $this->assertEquals(array_column($expectedProducts, 'id'), array_column($products, 'id'));
        $this->assertEquals(array_column($expectedProducts, 'category'), array_column($products, 'category'));
        $this->assertEquals(array_column($expectedPrices, $currency), array_column($prices, 'discounted_price'));
    }

    /**
     * @param string $currency
     *
     * @return array
     */
    private function findByCurrency(string $currency): array
    {
        $resultJson = $this->productAggregator->find($currency, 1, 10);

        return json_decode($resultJson, true);
    }

    /**
     * @return array
     */
    private function firstTenResult(): array
    {
        return [
            [
                'id' => '499bae93-b4f3-4f58-b32a-fc38a68c0724',
                'category' => [
                    'id' => 'b6fb0f4d-ed6d-4832-91d2-d4ba5d0106a5',
                    'title' => 'Chevrolet'
                ],
                'price' => [
                    'default' => 386747.8,
                    'UAH' => 386586.8,
                    'USD' => 15076.89,
                    'EUR' => 13530.54,
                    'RUB' => 978064.61
                ]
            ],
            [
                'id' => '5690d67b-3cb9-481e-b781-71905e22f990',
                'category' => [
                    'id' => '6b669175-22a1-4f04-83e9-cdf698224fe0',
                    'title' => 'Cadillac'
                ],
                'price' => [
                    'default' => 447524.69,
                    'UAH' => 447000.0,
                    'USD' => 17433.0,
                    'EUR' => 15645.0,
                    'RUB' => 1130910.0
                ]
            ],
            [
                'id' => 'e9e254dd-d23c-40e2-a3a5-b315a44b8e12',
                'category' => [
                    'id' => 'bfd8740b-bd83-4585-930b-658f6824c4c8',
                    'title' => 'Jeep'
                ],
                'price' => [
                    'default' => 111351.44,
                    'UAH' => 94648.73,
                    'USD' => 3691.31,
                    'EUR' => 3312.71,
                    'RUB' => 239461.28
                ]
            ],
            [
                'id' => '28dae648-119a-408a-93db-2f666250ad3a',
                'category' => [
                    'id' => 'a298ba1f-a4fe-4dbb-b412-2fc3cd1ca501',
                    'title' => 'Subaru'
                ],
                'price' => [
                    'default' => 26456.04,
                    'UAH' => 26456.04,
                    'USD' => 1031.79,
                    'EUR' => 925.97,
                    'RUB' => 66933.79
                ]
            ],
            [
                'id' => 'cf77749d-c685-47dc-8dc5-9738dd43a669',
                'category' => [
                    'id' => 'c55df5b4-f8c3-40f6-a786-37f8454d21e3',
                    'title' => 'Saab'
                ],
                'price' => [
                    'default' => 274082.64,
                    'UAH' => 274082.64,
                    'USD' => 10689.23,
                    'EUR' => 9592.9,
                    'RUB' => 693429.08
                ]
            ],
            [
                'id' => '0096f536-0ad5-4132-b03e-9cf89b57a565',
                'category' => [
                    'id' => '01175238-eb73-4bb1-867d-6c5d79ac294f',
                    'title' => 'Lincoln'
                ],
                'price' => [
                    'default' => 343021.49,
                    'UAH' => 342766.49,
                    'USD' => 13367.9,
                    'EUR' => 11996.83,
                    'RUB' => 867199.22
                ]
            ],
            [
                'id' => 'ca0bd999-2cb0-43cf-9aea-202a38b20b7e',
                'category' => [
                    'id' => '01175238-eb73-4bb1-867d-6c5d79ac294f',
                    'title' => 'Lincoln'
                ],
                'price' => [
                    'default' => 750917.26,
                    'UAH' => 750662.26,
                    'USD' => 29275.83,
                    'EUR' => 26273.18,
                    'RUB' => 1899175.52
                ]
            ],
            [
                'id' => '55c6ab9c-3369-46fd-831f-728634b65772',
                'category' => [
                    'id' => '01175238-eb73-4bb1-867d-6c5d79ac294f',
                    'title' => 'Lincoln'
                ],
                'price' => [
                    'default' => 914273.94,
                    'UAH' => 914018.94,
                    'USD' => 35646.74,
                    'EUR' => 31990.67,
                    'RUB' => 2312467.92
                ]
            ],
            [
                'id' => '416254a0-db10-4de8-987e-4d8628e5c2ed',
                'category' => [
                    'id' => 'dfa4db16-9286-47d8-a8c5-08e3584f77ad',
                    'title' => 'Toyota'
                ],
                'price' => [
                    'default' => 139099.84,
                    'UAH' => 104324.88,
                    'USD' => 4068.68,
                    'EUR' => 3651.38,
                    'RUB' => 263941.95
                ]
            ],
            [
                'id' => '28e90e82-e828-49a7-9e88-510da0634153',
                'category' => [
                    'id' => 'c3f8ea98-6c40-4f42-b0d3-1d8b3ecce8a8',
                    'title' => 'Ford'
                ],
                'price' => [
                    'default' => 738953.13,
                    'UAH' => 738772.13,
                    'USD' => 28812.12,
                    'EUR' => 25857.03,
                    'RUB' => 1869093.49
                ]
            ],
        ];
    }
}