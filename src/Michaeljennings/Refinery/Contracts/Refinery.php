<?php namespace Michaeljennings\Refinery\Contracts;

use Closure;

interface Refinery {

	/**
	 * Refine the item(s) using the set template
	 * 
	 * @param  mixed $raw
	 * @return mixed
	 */
	public function refine($raw);

	/**
	 * Refine a collection of raw items
	 * 
	 * @param  mixed $raw
     * @param  Closure $callback
	 * @return array
	 */
	public function refineCollection($raw, Closure $callback);

	/**
	 * Attach the relational properties to a refined item
	 * 
	 * @param  mixed $raw 
	 * @param  mixed $refined   
	 * @return mixed
	 */
	public function attachRelations($raw, $refined);

	/**
	 * Set the relations you want to bring with the refined items
	 * 
	 * @param  string|array $relations
	 * @return Refinery
	 */
	public function bring($relations);

	/**
	 * Set the class to be used for the relationship
	 * 
	 * @param  string  $relationalClass 
	 * @return string 
	 */
	public function relationship($relationalClass);
	
}