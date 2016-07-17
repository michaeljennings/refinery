<?php

namespace Michaeljennings\Refinery\Contracts;

interface Refinery
{
    /**
     * Refine the item(s) using the set template.
     *
     * @param  mixed $raw
     * @return mixed
     */
    public function refine($raw);

    /**
     * Refine a collection of raw items.
     *
     * @param  mixed $raw
     * @return array
     */
    public function refineCollection($raw);

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
     * @param callable|bool $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function attach($className, callable $callback = false);

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function embed($className, callable $callback = false);

    /**
     * Alias for the attach method.
     *
     * @param string   $className
     * @param callable $callback
     * @return array
     * @throws \Michaeljennings\Refinery\Exceptions\AttachmentClassNotFound
     */
    public function nest($className, callable $callback = false);
}