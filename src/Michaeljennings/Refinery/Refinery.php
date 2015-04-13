<?php namespace Michaeljennings\Refinery;

use Closure, Traversable;
use Michaeljennings\Refinery\Contracts\Refinery as RefineryContract;

abstract class Refinery implements RefineryContract {

	/**
	 * The template for the refinery
	 * 
	 * @var Closure
	 */
	protected $refineryTemplate;

	/**
	 * The refinery relationships
	 * 
	 * @var array
	 */
	protected $relations = array();

	/**
	 * A query to run on the raw data
	 * 
	 * @var Closure
	 */
	protected $query;

	public function __construct()
	{
		$this->refineryTemplate = $this->setTemplate();
	}

	/**
	 * Set the template the refinery will use for each item passed to it
	 *
	 * @return Closure
	 */
	abstract protected function setTemplate();

	/**
	 * Refine the item(s) using the set template
	 * 
	 * @param  mixed $raw
	 * @return mixed
	 */
	public function refine($raw)
	{
		$callback = $this->refineryTemplate;

		if ( ! $callback instanceof Closure) {
			throw new Exceptions\RefineryTemplateException("The refinery template must be a closure.");
		}

		if ( is_array($raw) || $raw instanceof Traversable ) {
			return $this->refineCollection($raw, $callback);
		}

		return $this->refineItem($raw, $callback);
	}

	/**
	 * Refine a single item using the supplied callback.
	 * 
	 * @param  mixed $raw
	 * @param  Closure $callback
	 * @return mixed
	 */
	public function refineItem($raw, Closure $callback)
	{
		$refined = $callback($raw);

		if (!empty($this->relations)) {
			$refined = $this->attachRelations($raw, $refined);
		}
		
		return $refined;
	}

	/**
	 * Refine a collection of raw items.
	 * 
	 * @param  mixed $raw
	 * @return array
	 */
	public function refineCollection($raw, Closure $callback)
	{
		$refined = array();

		foreach ($raw as $item) {
			$refined[] = $this->refineItem($item, $callback);
		}

		return $refined;
	}

	/**
	 * Attach the relational properties to a refined item.
	 * 
	 * @param  mixed $raw 
	 * @param  mixed $refined   
	 * @return mixed
	 */
	public function attachRelations($raw, $refined)
	{
		foreach ($this->relations as $relation => $relationalClass) {
			if ($relationalClass->hasQuery()) {
				$query = $relationalClass->getQuery();
				$items = $query($raw->$relation());
				if ( ! $items instanceof \Illuminate\Support\Collection) {
					$items = $items->get();
				}
			} else {
				$items = $raw->$relation;
			}

			$refined[$relation] = !is_null($items) ? $relationalClass->refine($items) : null;
		}

		return $refined;
	}

	/**
	 * Set the relations you want to bring with the refined items
	 * 
	 * @param  string|array $relations
	 * @return Refinery
	 */
	public function bring($relations) 
	{
		if (is_string($relations)) $relations = func_get_args();

		$this->relations = $this->parseRelations($relations);

		return $this;
	}

	/**
	 * Set a query to run on the raw data
	 * 
	 * @param  Closure $query
	 * @return Refinery
	 */
	public function query(Closure $query)
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * Get the classes needed for each relationship
	 * 
	 * @param  array  $relations
	 * @return array
	 */
	private function parseRelations(array $relations)
	{
		$parsedRelations = array();

		foreach ($relations as $key => $relation) {
			if (!is_numeric($key)) {
				if ($relation instanceof Closure) {
					$parsedRelations[$key] = $this->getRelation($key);
					$relation($parsedRelations[$key]);
				} else {
					$parsedRelations[$key] = $this->getRelation($key);
					$parsedRelations[$key]->bring($relation);
				}
			} else {
				$parsedRelations[$relation] = $this->getRelation($relation);
			}
		}

		return $parsedRelations;
	}

	/**
	 * Check the relational method is set on the child class, if it is the run
	 * the method
	 * 
	 * @param  string $relation 
	 * @return mixed
	 */
	protected function getRelation($relation)
	{
		if (!method_exists($this, $relation)) {
			throw new Exceptions\RefineryRelationshipException('The "'.$relation.'" 
				relationship is not specified on that refinery.');
		}

		return $this->$relation();
	}

	/**
	 * Set the class to be used for the relationship
	 * 
	 * @param  string  $relationalClass 
	 * @return string 
	 */
	public function relationship($relationalClass)
	{
		if ( ! class_exists($relationalClass)) {
			throw new Exceptions\RefineryRelationshipException('The "'.$relationalClass.'" 
				class does not exist.');
		}

		return new $relationalClass;
	}

	/**
	 * Check if a query has been set
	 * 
	 * @return boolean
	 */
	public function hasQuery()
	{
		return ! empty($this->query);
	}

	/**
	 * Return the query callback
	 * 
	 * @return Closure
	 */
	public function getQuery()
	{
		return $this->query;
	}
}