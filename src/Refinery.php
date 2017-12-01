<?php

namespace Michaeljennings\Refinery;

use Traversable;
use Michaeljennings\Refinery\Exceptions\RefineryMethodNotFound;
use Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound;
use Michaeljennings\Refinery\Contracts\Refinery as RefineryContract;

abstract class Refinery implements RefineryContract
{
    /**
     * The items to be attached to the refined item.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * A filter to run on the raw data.
     *
     * @var callable
     */
    protected $filter;

    /**
     * The extra data which is available within a refinery.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Set the template the refinery will use for each item passed to it
     *
     * @param mixed $item
     * @return mixed
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
        if ((is_array($raw) && $this->isMultidimensional($raw)) || $raw instanceof Traversable) {
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
        $refined = [];

        foreach ($raw as $item) {
            $refined[] = $this->refineItem($item);
        }

        return $refined;
    }

    /**
     * Return any required attachments. Check if the attachment needs to
     * be filtered, if so they run the callback and then return the
     * attachments.
     *
     * @param  mixed $raw
     * @return mixed
     */
    protected function includeAttachments($raw)
    {
        $attachments = [];

        foreach ($this->attachments as $attachment => $refinery) {
            if (isset($refinery['raw'])) {
                $attachments[$attachment] = $refinery['raw']($raw);
            } else {
                $class = $refinery['class'];
                $callback = $refinery['callback'];

                if ( ! $callback) {
                    $items = $this->getItems($raw, $class, $attachment);
                } else {
                    $items = $this->getItemsUsingCallback($raw, $class, $callback);
                }

                $attachments[$attachment] = ! is_null($items) ? $class->refine($items) : null;
            }
        }

        return $attachments;
    }

    /**
     * Get the items to be refined from the raw object.
     *
     * @param mixed  $raw
     * @param mixed  $class
     * @param string $attachment
     * @return mixed
     */
    protected function getItems($raw, $class, $attachment)
    {
        if ($class->hasFilter()) {
            $query = $class->getFilter();

            return call_user_func_array($query, [$raw->$attachment()]);
        } else {
            return is_object($raw) ? $raw->$attachment : $raw[$attachment];
        }
    }

    /**
     * Run a callback on the raw item(s) and then return the items.
     *
     * @param mixed    $raw
     * @param mixed    $class
     * @param callable $callback
     * @return mixed
     */
    protected function getItemsUsingCallback($raw, $class, callable $callback)
    {
        if ($class->hasFilter()) {
            $query = $class->getFilter();
            $items = $callback($raw);

            return call_user_func_array($query, [$items]);
        } else {
            return $callback($raw);
        }
    }

    /**
     * Set the attachments you want to bring with the refined items.
     *
     * @param  string|array $attachments
     * @return $this
     */
    public function bring($attachments)
    {
        if (is_string($attachments)) {
            $attachments = func_get_args();
        }

        $this->attachments = $this->parseAttachments($attachments);

        return $this;
    }

    /**
     * Get the classes needed for each attachment.
     *
     * @param  array $relations
     * @return array
     */
    protected function parseAttachments(array $relations)
    {
        $parsedRelations = [];

        foreach ($relations as $key => $relation) {
            if ( ! is_numeric($key)) {
                if (is_callable($relation)) {
                    $parsedRelations[$key] = $this->attachItem($key);
                    $parsedRelations[$key]['class']->with($this->attributes)->filter($relation);
                } else {
                    $parsedRelations[$key] = $this->attachItem($key);
                    $parsedRelations[$key]['class']->with($this->attributes)->bring($relation);
                }
            } else {
                $parsedRelations[$relation] = $this->attachItem($relation);

                if (isset($parsedRelations[$relation]['class'])) {
                    $parsedRelations[$relation]['class']->with($this->attributes);
                }
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
     * @param string|callable $className
     * @param callable|bool   $callback
     * @return array
     * @throws AttachmentClassNotFound
     */
    public function attach($className, callable $callback = null)
    {
        // If the user has passed through a callable item then we want
        // to attach the raw result of that call.
        if (is_callable($className)) {
            return ['raw' => $className];
        }

        if ( ! class_exists($className)) {
            throw new AttachmentClassNotFound("No class found with the name '{$className}'.");
        }

        return ['class' => new $className, 'callback' => $callback];
    }

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws AttachmentClassNotFound
     */
    public function embed($className, callable $callback = null)
    {
        return $this->attach($className, $callback);
    }

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws AttachmentClassNotFound
     */
    public function nest($className, callable $callback = null)
    {
        return $this->attach($className, $callback);
    }

    /**
     * Set a filter to run on the raw data.
     *
     * @param  callable $filter
     * @return Refinery
     */
    protected function setFilter(callable $filter)
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
    protected function filter(callable $filter)
    {
        return $this->setFilter($filter);
    }

    /**
     * Check if a filter has been set
     *
     * @return boolean
     */
    protected function hasFilter()
    {
        return ! empty($this->filter);
    }

    /**
     * Return the filter callback
     *
     * @return Closure
     */
    protected function getFilter()
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

    /**
     * Check if the provided array is multidimensional.
     *
     * @param array $array
     * @return bool
     */
    protected function isMultidimensional(array $array)
    {
        foreach ($array as $element) {
            if ( ! is_array($element) && ! is_object($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store the passed items within the $attributes property.
     *
     * @param array $items
     * @return $this
     */
    public function with(array $items)
    {
        $this->attributes = $items;

        return $this;
    }

    /**
     * Use the __get magic method to access the items within the $attributes
     * array is if you were accessing a property on the class.
     *
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }
}