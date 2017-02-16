<?php
namespace Communibase\Query;

/**
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class ExpressionBuilder {

	/**
	 * @param $values
	 *
	 * @return array
	 */
	public function orX($values) {
		return [
				'$or' => $values
		];
	}

	/**
	 * @param $values
	 *
	 * @return array
	 */
	public function andX($values) {
		return [
				'$and' => $values
		];
	}

	/**
	 * @param $fields
	 * @param $comparison
	 *
	 * @return array
	 */
	private function comparison($fields, $comparison) {
		if (!is_array($fields)) {
			$fields = [$fields];
		}
		$query = [];
		foreach ($fields as $field) {
			$query[$field] = $comparison;
		}
		return $query;
	}

	/**
	 * @param string $fields
	 * @param string $value
	 *
	 * @return array
	 */
	public function like($fields, $value) {
		return $this->comparison($fields, [
				'$regex' => $value,
				'$options' => 'i',
		]);
	}

	/**
	 * Matches all values that are equal to the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function eq($fields, $value) {
		return $this->comparison($fields, $value);
	}

	/**
	 * Matches values that are greater than the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function gt($fields, $value) {
		return $this->comparison($fields, [
				'$gt' => $value
		]);
	}

	/**
	 * Matches values that are greater than or equal to the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function gte($fields, $value) {
		return $this->comparison($fields, [
				'$gte' => $value
		]);
	}

	/**
	 * Matches any of the values that exist in an array specified in the query.
	 *
	 * @param string|array $fields
	 * @param array $values
	 *
	 * @return array
	 */
	public function in($fields, $values) {
		return $this->comparison($fields, [
				'$in' => $values
		]);
	}

	/**
	 * Matches values that are less than the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function lt($fields, $value) {
		return $this->comparison($fields, [
				'$lt' => $value
		]);
	}

	/**
	 * Matches values that are less than or equal to the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function lte($fields, $value) {
		return $this->comparison($fields, [
				'$lte' => $value
		]);
	}

	/**
	 * Matches all values that are not equal to the value specified in the query.
	 *
	 * @param string|array $fields
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function ne($fields, $value) {
		return $this->comparison($fields, [
				'$ne' => $value
		]);
	}

	/**
	 * Matches values that do not exist in an array specified to the query.
	 *
	 * @param string|array $fields
	 * @param array $values
	 *
	 * @return array
	 */
	public function nin($fields, $values) {
		return $this->comparison($fields, [
				'$nin' => $values
		]);
	}

}