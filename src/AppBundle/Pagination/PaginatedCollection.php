<?php

namespace AppBundle\Pagination;


/**
 * Class PaginatedCollection
 * @package AppBundle\Pagination
 */
class PaginatedCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var
     */
    private $total;

    /**
     * @var int
     */
    private $count;

    /**
     * @var array
     */
    private $_links = [];

    /**
     * PaginatedCollection constructor.
     * @param array $items
     * @param $totalItems
     */
    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    /**
     * @param $ref
     * @param $url
     */
    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }
}
