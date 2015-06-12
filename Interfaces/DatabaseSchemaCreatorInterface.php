<?php

namespace Siso\Bundle\ContentLoaderBundle\Interfaces;

/**
 * Interface for database schema creator
 */
interface DatabaseSchemaCreatorInterface
{
    /**
     * Creates database schema
     *
     * @return void
     */
    public function createSchema();
}