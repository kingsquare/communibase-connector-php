<?php
namespace Communibase\Query;

/**
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class QueryBuilder {

	/**
	 * @var ExpressionBuilder
	 */
	private $expressionBuilder;

	/**
	 * @var array
	 */
	private $query = [];

	/**
	 * @param array $expr
	 *
	 * @return self
	 */
	public function add($expr) {
		$this->query = array_merge($this->query, $expr);
		return $this;
	}

	/**
	 * @param array $expr
	 *
	 * @return self
	 */
	public function addOr($expr) {
		$this->query['$or'][] = $expr;
		return $this;
	}

	/**
	 * @param array $expr
	 *
	 * @return self
	 */
	public function addAnd($expr) {
		$this->query['$and'][] = $expr;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return ExpressionBuilder
	 */
	public function expr() {
		if (empty($this->expressionBuilder)) {
			$this->expressionBuilder = new ExpressionBuilder();
		}
		return $this->expressionBuilder;
	}

}
