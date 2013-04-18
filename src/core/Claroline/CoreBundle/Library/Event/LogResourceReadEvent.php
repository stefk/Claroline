<?php

namespace Claroline\CoreBundle\Library\Event;

class LogResourceReadEvent extends LogGenericEvent implements NotRepeatableLog
{
    const ACTION = 'resource_read';

    /**
     * Constructor.
     */
    public function __construct($resource)
    {
        parent::__construct(
            self::ACTION,
            array(
                'resource' => array(
                    'name' => $resource->getName(),
                    'path' => $resource->getPathForDisplay()
                ),
                'workspace' => array(
                    'name' => $resource->getWorkspace()->getName()
                ),
                'owner' => array(
                    'last_name' => $resource->getCreator()->getLastName(),
                    'first_name' => $resource->getCreator()->getFirstName()
                )
            ),
            null,
            null,
            $resource,
            null,
            $resource->getWorkspace(),
            $resource->getCreator()
        );
    }

    public function getLogSignature()
    {
        return $this->resource->getId();
    }
}