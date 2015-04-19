<?php namespace Michaeljennings\Refinery\Contracts;

use Closure;

interface Refinery {

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
     * Return any required attachments. Check if the attachment needs to be filtered,
     * if so they run the callback and then return the attachments.
     *
     * @param  mixed $raw
     * @return mixed
     */
    public function includeAttachments($raw);

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
     * @param  string $className
     * @return mixed
     *
     * @throws AttachmentClassNotFound
     */
    public function attach($className);
	
}