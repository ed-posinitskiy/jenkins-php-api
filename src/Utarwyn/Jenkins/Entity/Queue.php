<?php

namespace Utarwyn\Jenkins\Entity;

use Utarwyn\Jenkins\Error\ConnectionErrorException;
use Utarwyn\Jenkins\JenkinsEntity;

/**
 * Class Queue
 *
 * @package Utarwyn\Jenkins\Entity
 */
class Queue extends JenkinsEntity
{
    protected $_items;

    protected $_discoverableItems;

    public function __construct($client)
    {
        parent::__construct($client, "queue");

        foreach ($this->getData()->items as $item) {
            $this->_items[] = new QueueItem($item);
        }

        foreach ($this->getData()->discoverableItems as $item) {
            $this->_items[] = new QueueItem($item);
        }
    }

    public function getItems(): array
    {
        return $this->_items;
    }

    public function getDiscoverableItems(): array
    {
        return $this->_discoverableItems;
    }

    public function getItem(int $id): ?QueueItem
    {
        foreach ($this->_items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function pullItem(int $id): ?QueueItem
    {
        try {
            $object = $this->client->get(sprintf('queue/item/%u', $id));
        } catch (ConnectionErrorException $e) {
            return null;
        }

        return new QueueItem($object);
    }
}
