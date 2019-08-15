<?php

namespace Horoshop;

use Horoshop\Exceptions\UnavailablePageException;

class ProductAggregator
{
    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

		public function getCategoryNameById(string $category_id, array $array): string // Пошук назви кадегорії за ідентифікатором
		{
			$result = '';
			foreach ($array['categories'] as $category) {
				if ($category['id'] == $category_id) {
					$result = $category['title'];
				}
			}
			return $result;
		}

		public function getDiscounts(array $array, string $product_id, string $category_id): array // Пошук наявних знижок відносно категорій та товарів
		{
			$discount_array = [];
			foreach ($array['discounts'] as $discount) {
				if ($product_id == $discount['related_id']) {
					$discount_array['product']['type'] = $discount['type'];
					$discount_array['product']['value'] = $discount['value'];
				}
				if ($category_id == $discount['related_id']) {
					$discount_array['category']['type'] = $discount['type'];
					$discount_array['category']['value'] = $discount['value'];
				}
			}
			return $discount_array;
		}

		public function getDiscountArray(array $array, string $product_id, string $category_id, float $amount): array // Масив із докладними даними про знижки
		{
			$discount_array = $this->getDiscounts($array, $product_id, $category_id);
			$product_discount = 0;
			$category_discount = 0;
			$discounted_prices_array = [];
			$product_discount_type = '';
			$category_discount_type = '';
			$product_discount_original_value = 0;
			$category_discount_original_value = 0;

			if (array_key_exists('product', $discount_array)) {
				if ($discount_array['product']['type'] == 'absolute') {
							$product_discount = $discount_array['product']['value'];
							$product_discount_type = 'absolute';
							$product_discount_original_value = $discount_array['product']['value'];
				}
			}
			if (array_key_exists('product', $discount_array)) {
				if ($discount_array['product']['type'] == 'percent') {
							$product_discount = $amount * $discount_array['product']['value'] / 100;
							$product_discount_type = 'percent';
							$product_discount_original_value = $discount_array['product']['value'];
				}
			}
			if (array_key_exists('category', $discount_array)) {
				if ($discount_array['category']['type'] == 'absolute') {
							$category_discount = $discount_array['category']['value'];
							$category_discount_type = 'absolute';
							$category_discount_original_value = $discount_array['category']['value'];
				}
			}
			if (array_key_exists('category', $discount_array)) {
				if ($discount_array['category']['type'] == 'percent') {
							$category_discount = $amount * $discount_array['category']['value'] / 100;
							$category_discount_type = 'percent';
							$category_discount_original_value = $discount_array['category']['value'];
				}
			}

			if ($product_discount > $category_discount) { // Заповнюємо результуючий масив з даними знижок в залежності від того, яка знижка є більшою
				$discounted_prices_array['relation'] = 'product';
				$discounted_prices_array['discount_type'] = $product_discount_type;
				$discounted_prices_array['original_value'] = $product_discount_original_value;
				$discounted_prices_array['uah_amount'] = $amount - $product_discount;
			} else {
				$discounted_prices_array['relation'] = 'category';
				$discounted_prices_array['discount_type'] = $category_discount_type;
				$discounted_prices_array['original_value'] = $category_discount_original_value;
				$discounted_prices_array['uah_amount'] = $amount - $category_discount;
			}
			return $discounted_prices_array;
		}

		public function getCurrentCurrencyAmount(array $array, float $amount, string $currency): float // Перераховуємо суму валюти залежно від курсу
		{
			if ($currency == 'UAH') {
				$current_currency_amount = ceil($amount * 100) / 100;
			} else {
				$multiplier = $array['currencies'][0]['rates'][$currency];
				$current_currency_amount = ceil($amount * $multiplier * 100) / 100;
			}

			return $current_currency_amount;
		}

		/**
     * @param string $currency
     * @param int    $page
     * @param int    $perPage
     *
     * @return string Json
     * @throws UnavailablePageException
     */

		public function find(string $currency, int $page, int $perPage): string
		{
			$result = '';
			$json = file_get_contents($this->filename);
			$array = json_decode($json, TRUE);

			$items = [];
			$data = $array['products'];
			$pages = ceil(count($data) / $perPage); // Загальна кількість сторінок

			if ($page > $pages || $page <= 0) { // Перевіряємо переданий номер сторінки
				throw new UnavailablePageException("Вказано невірний номер сторінки!");
			}

			foreach (array_slice($data, ($page - 1) * $perPage, $perPage) as $product) { // Заповнюємо результуючий json відповідно до умов задачі
				$item = [];
				$discount_info = [];
				$item['id'] = $product['id'];
				$item['title'] = $product['title'];
				$item['category']['id'] = $product['category'];
				$item['category']['title'] = $this->getCategoryNameById($product['category'], $array);
				$item['price']['amount'] = $this->getCurrentCurrencyAmount($array, $product['amount'], $currency);
				$discount_info = $this->getDiscountArray($array, $item['id'], $item['category']['id'], $product['amount']);
				$item['price']['discounted_price'] = $this->getCurrentCurrencyAmount($array, $discount_info['uah_amount'], $currency);
				$item['price']['currency'] = $currency;
				if ($item['price']['amount'] > $item['price']['discounted_price']) { // Якщо на товар є знижка - відображаємо секцію з інформацією про неї
					$item['price']['discount']['type'] = $discount_info['discount_type'];
					if ($item['price']['discount']['type'] == 'absolute') {
						$item['price']['discount']['value'] = $this->getCurrentCurrencyAmount($array, $discount_info['original_value'], $currency);
					} else {
						$item['price']['discount']['value'] = $discount_info['original_value'];
					}
					$item['price']['discount']['relation'] = $discount_info['relation'];
				}
				$items['items'][] = $item;
			}
			$items['perPage'] = $perPage;
			$items['pages'] = $pages;
			$items['page'] = $page;
			$result = json_encode($items, JSON_PRETTY_PRINT); // Отримуємо результуючий json-рядок

			return $result;
		}
}
