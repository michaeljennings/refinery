<?php namespace Michaeljennings\Refinery;

use Closure, Traversable;
use Michaeljennings\Refinery\Exceptions\RefineryMethodNotFound;
use Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound;
use Michaeljennings\Refinery\Contracts\Refinery as RefineryContract;

abstract class Refinery implements RefineryContract {

	/**
	 * The items to be attached to the refined item.
	 * 
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * A filter to run on the raw data.
	 * 
	 * @var callable
	 */
	protected $filter;

	/**
	 * Set the template the refinery will use for each item passed to it
	 *
     * @param mixed $item
	 * @return Closure
	 */
	abstract protected function setTemplate($item);

	/**
	 * Refine the item(s) using the set template.
	 * 
	 * @param  mixed $raw
	 * @return mixed
	 */
	public function refine($raw)
	{
		if ($raw instanceof Traversable) {
			return $this->refineCollection($raw);
		}

		return $this->refineItem($raw);
	}

	/**
	 * Refine a single item using the supplied callback.
	 * 
	 * @param  mixed $raw
	 * @return mixed
	 */
	public function refineItem($raw)
	{
		$refined = $this->setTemplate($raw);

		if ( ! empty($this->attachments)) {
			$refined = $this->merge($refined, $this->includeAttachments($raw));
		}
		
		return $refined;
	}

	/**
	 * Refine a collection of raw items.
	 *
     * @param mixed $raw
	 * @return array
	 */
	public function refineCollection($raw)
	{
		$refined = array();

		foreach ($raw as $item) {
			$refined[] = $this->refineItem($item);
		}

		return $refined;
	}

	/**
	 * Return any required attachments. Check if the attachment needs to be filtered,
     * if so they run the callback and then return the attachments.
	 * 
	 * @param  mixed $raw
	 * @return mixed
	 */
	public function includeAttachments($raw)
	{
        $attachments = [];

		foreach ($this->attachments as $attachment => $class) {
			if ($class->hasFilter()) {
				$query = $class->getFilter();
                $items = call_user_func_array($query, [$raw->$attachment()]);
			} else {
				$items = $raw->$attachment;
			}

            $attachments[$attachment] = ! is_null($items) ? $class->refine($items) : null;
		}

		return $attachments;
	}

	/**
	 * Set the attachments you want to bring with the refined items.
	 * 
	 * @param  string|array $attachments
	 * @return $this
	 */
	public function bring($attachments)
	{
		if (is_string($attachments)) $attachments = func_get_args();

		$this->attachments = $this->parseAttachments($attachments);

		return $this;
	}

	/**
	 * Get the classes needed for each attachment.
	 * 
	 * @param  array  $relations
	 * @return array
	 */
	private function parseAttachments(array $relations)
	{
		$parsedRelations = array();

		foreach ($relations as $key => $relation) {
			if ( ! is_numeric($key)) {
				if ($relation instanceof Closure) {
					$parsedRelations[$key] = $this->attachItem($key);
                    $parsedRelations[$key]->filter($relation);
				} else {
					$parsedRelations[$key] = $this->attachItem($key);
					$parsedRelations[$key]->bring($relation);
				}
			} else {
				$parsedRelations[$relation] = $this->attachItem($relation);
			}
		}

		return $parsedRelations;
	}

	/**
	 * Check the attachment method exists on the class being attached, if it
     * does then run the method.
	 * 
	 * @param  string $attachment
	 * @return mixed
     *
     * @throws RefineryMethodNotFound
	 */
	protected function attachItem($attachment)
	{
		if ( ! method_exists($this, $attachment)) {
			throw new RefineryMethodNotFound(
                "No attachment set with the name '{$attachment}' on '" . get_class($this) . "'."
            );
		}

		return $this->$attachment();
	}

	/**
	 * Set the class to be used for the attachment.
	 * 
	 * @param  string $className
	 * @return mixed
     *
     * @throws AttachmentClassNotFound
	 */
	public function attach($className)
	{
		if ( ! class_exists($className)) {
            throw new AttachmentClassNotFound("No class found with the name '{$className}'.");
		}

        return new $className;
	}

    /**
     * Set a filter to run on the raw data.
     *
     * @param  callable $filter
     * @return Refinery
     */
    public function setFilter(Closure $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Alias for the setFilter method.
     *
     * @param  callable $filter
     * @return Refinery
     */
    public function filter(Closure $filter)
    {
        return $this->setFilter($filter);
    }

	/**
	 * Check if a filter has been set
	 * 
	 * @return boolean
	 */
	public function hasFilter()
	{
		return ! empty($this->filter);
	}

	/**
	 * Return the filter callback
	 * 
	 * @return Closure
	 */
	public function getFilter()
	{
		return $this->filter;
	}

    /**
     * Merge a set of data into another.
     *
     * @param mixed $original
     * @param mixed $merge
     * @return mixed
     */
    protected function merge($original, $merge)
    {
        if (is_array($original) && is_array($merge)) {
            return array_merge($original, $merge);
        }

        if (is_array($original)) {
            foreach ($merge as $key => $value) {
                $original[$key] = $value;
            }
        } else {
            foreach ($merge as $key => $value) {
                $original->$key = $value;
            }
        }

        return $original;
    }

}
