<?php

namespace Michaeljennings\Refinery\Contracts;

interface Refinery
{
    /**
     * Refine the item(s) using the set template.
     *
     * @param  mixed $raw
     * @param  boolean $retainKey
     * @return mixed
     */
    public function refine($raw, $retainKey);

    /**
     * Refine a collection of raw items.
     *
     * @param  mixed $raw
     * @param  boolean $retainKey
     * @return array
     */
    public function refineCollection($raw, $retainKey);

    /**
     * Set the attachments you want to bring with the refined items.
     *
     * @param  string|array $attachments
     * @return $this
     */
    public function bring($attachments);

    /**
     * Set the class to be used for the attachment.
     *
     * @param string        $className
     * @param callable $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function attach($className, callable $callback = null);

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function embed($className, callable $callback = null);

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function nest($className, callable $callback = null);
}