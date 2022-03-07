<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use whikloj\archivematicaPhp\Exceptions\ArchivematicaException;
use whikloj\archivematicaPhp\Exceptions\AuthorizationException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Storage\Location;
use whikloj\archivematicaPhp\Storage\LocationImpl;
use whikloj\archivematicaPhp\Storage\Pipeline;
use whikloj\archivematicaPhp\Storage\PipelineImpl;
use whikloj\archivematicaPhp\Storage\Space;
use whikloj\archivematicaPhp\Storage\SpaceImpl;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

class ArchivematicaImpl implements ArchivematicaInstance
{
    /**
     * @var string The Archivematica instance base URL
     */
    private $archivematica_url;

    /**
     * @var string The Storage Service instance base URL
     */
    private $storage_url;

    /**
     * @var ?string The Archivematica API username
     */
    private $am_api_user = null;

    /**
     * @var ?string The Archivematica API key
     */
    private $am_api_key = null;

    /**
     * @var ?string The Storage Service API username
     */
    private $ss_api_user = null;

    /**
     * @var ?string The Storage Service API key
     */
    private $ss_api_key = null;

    /**
     * @var Client A client setup to access the Archivematica instance.
     */
    private $am_client;

    /**
     * @var Client A client setup to access the Storage Service instance.
     */
    private $ss_client;

    /**
     * @var ?\whikloj\archivematicaPhp\Transfer A transfer instance
     */
    private $transfer = null;

    /**
     * @var ?\whikloj\archivematicaPhp\Ingest An ingest instance
     */
    private $ingest = null;

    /**
     * @var ?Space A space instance
     */
    private $space = null;

    /**
     * @var ?Pipeline A pipeline instance
     */
    private $pipeline = null;

    /**
     * @var ?Location A location instance
     */
    private $location = null;

    /**
     * @var ?\whikloj\archivematicaPhp\Package A package instance
     */
    private $package = null;

    /**
     * @var ?LoggerInterface A logger.
     */
    private $logger;

    /**
     * Create a new Archivematica instance.
     *
     * @param string $base_url
     *   The Url of the Archivematica server
     * @param string $storage_url
     *   The Url of the Storage server.
     * @throws \whikloj\archivematicaPhp\Exceptions\ArchivematicaException
     *   On error parsing the provided URL.
     */
    private function __construct(string $base_url, string $storage_url)
    {
        if (parse_url($base_url) === false) {
            throw new ArchivematicaException(
                "Url ($base_url) is not a valid URL."
            );
        }
        if (parse_url($storage_url) === false) {
            throw new ArchivematicaException(
                "Url ($storage_url) is not a valid URL."
            );
        }
        $this->archivematica_url = rtrim($base_url, ' /');
        $this->storage_url = rtrim($storage_url, '/');
        $this->setupGuzzle();
        $this->disableLogging(); // Setup nullLogger by default.
    }

    /**
     * Sets up the two Guzzle clients including middleware to automatically add the
     * Authorization headers if missing.
     */
    private function setupGuzzle()
    {
        $am_stack = HandlerStack::create();
        $am_stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            if (
                !$request->hasHeader('Authorization')
            ) {
                $request = $request->withHeader('Authorization', $this->getAMAuthorization());
            }
            return $request;
        }));
        $this->am_client = new Client(['base_uri' => $this->archivematica_url, 'handler' => $am_stack]);
        $ss_stack = HandlerStack::create();
        $ss_stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            if (
                !$request->hasHeader('Authorization')
            ) {
                $request = $request->withHeader('Authorization', $this->getSSAuthorization());
            }
            return $request;
        }));
        $this->ss_client = new Client(['base_uri' => $this->storage_url, 'handler' => $ss_stack]);
    }

    /**
     * {@inheritDoc}
     */
    public static function create(string $am_url, string $ss_url): ArchivematicaInstance
    {
        return new ArchivematicaImpl($am_url, $ss_url);
    }

    /**
     * {@inheritDoc}
     */
    public function getArchivematicaUrl(): string
    {
        return $this->archivematica_url;
    }

    /**
     * {@inheritDoc}
     */
    public function getStorageServiceUrl(): string
    {
        return $this->storage_url;
    }

    /**
     * {@inheritDoc}
     */
    public function getAMUsername(): string
    {
        return $this->am_api_user;
    }

    /**
     * {@inheritDoc}
     */
    public function getAMKey(): string
    {
        return $this->am_api_key;
    }

    /**
     * {@inheritDoc}
     */
    public function getSSUsername(): string
    {
        return $this->ss_api_user;
    }

    /**
     * {@inheritDoc}
     */
    public function getSSKey(): string
    {
        return $this->ss_api_key;
    }

    /**
     * {@inheritDoc}
     */
    public function setAMCreds(
        string $username,
        string $key
    ): ArchivematicaInstance {
        $this->am_api_user = $username;
        $this->am_api_key = $key;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSSCreds(
        string $username,
        string $key
    ): ArchivematicaInstance {
        $this->ss_api_user = $username;
        $this->ss_api_key = $key;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTransfer(): Transfer
    {
        if (is_null($this->transfer)) {
            $this->transfer = new TransferImpl($this->am_client, $this->logger);
        }
        return $this->transfer;
    }

    /**
     * {@inheritDoc}
     */
    public function getIngest(): Ingest
    {
        if (is_null($this->ingest)) {
            $this->ingest = new IngestImpl($this->am_client, $this->logger);
        }
        return $this->ingest;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpace(): Space
    {
        if (is_null($this->space)) {
            $this->space = new SpaceImpl($this->ss_client);
        }
        return $this->space;
    }

    /**
     * {@inheritDoc}
     */
    public function getPipeline(): Pipeline
    {
        if (is_null($this->pipeline)) {
            $this->pipeline = new PipelineImpl($this->ss_client);
        }
        return $this->pipeline;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocation(): Location
    {
        if (is_null($this->location)) {
            $this->location = new LocationImpl($this->am_client, $this->ss_client);
        }
        return $this->location;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackage(): Package
    {
        if (is_null($this->package)) {
            $this->package = new PackageImpl($this->am_client, $this->ss_client);
        }
        return $this->package;
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessingConfig(string $name): \DOMDocument
    {
        $dom = new \DOMDocument();
        try {
            $response = $this->am_client->get(
                "/api/v2/processing-configuration/$name"
            );
            $code = $response->getStatusCode();
            if ($code == 403) {
                throw new AuthorizationException(
                    "Invalid credentials or insufficient permissions: {$response->getReasonPhrase()}",
                    $code
                );
            } elseif ($code != 200) {
                throw new RequestException(
                    "Failed to get processing configuration for $name: {$response->getReasonPhrase()}",
                    $code
                );
            }
            $body = $response->getBody()->getContents();
            $dom->loadXML($body);
        } catch (GuzzleException $e) {
            ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $dom;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger): ArchivematicaInstance
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function disableLogging(): ArchivematicaInstance
    {
        $this->logger = new NullLogger();
        return $this;
    }

     /**
     * Get the Username and ApiKey header for this Archivematica instance.
     *
     * @return string
     */
    private function getAMAuthorization(): string
    {
        if (!is_null($this->am_api_user) && !is_null($this->am_api_key)) {
            return "ApiKey " . $this->am_api_user . ":" . $this->am_api_key;
        }
        return "";
    }

    /**
     * Get the Username and ApiKey header for this Storage service instance.
     *
     * @return string
     */
    private function getSSAuthorization(): string
    {
        if (!is_null($this->ss_api_user) && !is_null($this->ss_api_key)) {
            return "ApiKey " . $this->ss_api_user . ":" . $this->ss_api_key;
        }
        return "";
    }
}
