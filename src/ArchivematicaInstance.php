<?php

namespace whikloj\archivematicaPhp;

use Psr\Log\LoggerInterface;
use whikloj\archivematicaPhp\Storage\Location;
use whikloj\archivematicaPhp\Storage\Pipeline;
use whikloj\archivematicaPhp\Storage\Space;

/**
 * An Archivematica instance with endpoint configurations.
 * @author Jared Whiklo
 */
interface ArchivematicaInstance
{
    /**
     * Static constructor.
     *
     * @param string $am_url
     *   The base URL of the Archivematica instance
     * @param string $ss_url
     *   The base URL of the Storage service instance
     * @return \whikloj\archivematicaPhp\ArchivematicaInstance
     *   The new Archivematica client.
     * @throws \whikloj\archivematicaPhp\Exceptions\ArchivematicaException
     *   Error creating client.
     */
    public static function create(string $am_url, string $ss_url): ArchivematicaInstance;

    /**
     * @return string
     *   Return the Archivematica URL.
     */
    public function getArchivematicaUrl(): string;

    /**
     * @return string
     *   Return the Storage Service URL.
     */
    public function getStorageServiceUrl(): string;

    /**
     * @return string
     *   The Archivematica API username or null.
     */
    public function getAMUsername(): string;

    /**
     * @return string
     *   The Archivematica API key or null.
     */
    public function getAMKey(): string;

    /**
     * @return string
     *   Return the Storage service API username or null
     */
    public function getSSUsername(): string;

    /**
     * @return string
     *   The Storage service API key or null.
     */
    public function getSSKey(): string;

    /**
     * Fluent method to set Archivematica username and key
     * @param string $username
     *   Set the API username.
     * @return \whikloj\archivematicaPhp\ArchivematicaInstance
     *   The instance of the client.
     */
    public function setAMCreds(string $username, string $key): ArchivematicaInstance;

    /**
     * Fluent method to set Storage service username and key.
     * @param string $username
     *   The Storage service user
     * @param string $key
     *   The storage service key
     * @return \whikloj\archivematicaPhp\ArchivematicaInstance
     *   The instance of the client
     */
    public function setSSCreds(string $username, string $key): ArchivematicaInstance;

    /**
     * Get a transfer instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Transfer
     */
    public function getTransfer(): Transfer;

    /**
     * Get an ingest instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Ingest
     */
    public function getIngest(): Ingest;

    /**
     * Get a space instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Storage\Space
     */
    public function getSpace(): Space;

    /**
     * Get a pipeline instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Storage\Pipeline
     */
    public function getPipeline(): Pipeline;

    /**
     * Get a location instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Storage\Location
     */
    public function getLocation(): Location;

    /**
     * Get a package instance for requests.
     *
     * @return \whikloj\archivematicaPhp\Package
     */
    public function getPackage(): Package;

    /**
     * Get the processing configuration.
     *
     * @param string $name
     *   The processing configuration name.
     * @return \DOMDocument
     *   The configuration as an XML document.
     * @throws \whikloj\archivematicaPhp\Exceptions\AuthorizationException
     *   If the response was a 403 Forbidden.
     * @throws \whikloj\archivematicaPhp\Exceptions\RequestException
     *   If the response is fails otherwise.
     */
    public function getProcessingConfig(string $name): \DOMDocument;

    /**
     * Set the logger to use.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *   The logger to use.
     * @return \whikloj\archivematicaPhp\ArchivematicaInstance
     *   The instance of the client.
     */
    public function setLogger(LoggerInterface $logger): ArchivematicaInstance;

    /**
     * Disable logging. Replaces any configured logger with a NullLogger
     *
     * @return \whikloj\archivematicaPhp\ArchivematicaInstance
     *   The instance of the client.
     * @see \Psr\Log\NullLogger
     */
    public function disableLogging(): ArchivematicaInstance;
}
