<?php

namespace whikloj\archivematicaPhp;

use GuzzleHttp\Exception\GuzzleException;
use whikloj\archivematicaPhp\Exceptions\RequestException;
use whikloj\archivematicaPhp\Utils\ArchivmaticaUtils;

/**
 * Implementation of a transfer object
 * @author Jared Whiklo
 * @since 0.0.1
 */
class TransferImpl extends OperationImpl implements Transfer
{
    /**
     * {@inheritDoc}
     */
    public function start(
        string $name,
        string $type,
        string $accession,
        array $paths,
        array $row_ids
    ): string {
        ArchivmaticaUtils::isValidTransferType($type);
        $data = [
            "name" => $name,
            "type" => $type,
            "accession" => $accession,
            "paths" => self::getEncodedPaths($paths),
            "row_ids" => (count($row_ids) == 0 ? [''] : $row_ids),
        ];
        $output = "";
        try {
            $response = $this->am_client->post(
                '/api/v2/transfer/unapproved/',
                [
                    'form_params' => $data,
                ]
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                201,
                "Request to start transfer ({$name}) failed"
            );
            if (
                !is_array($body) ||
                !array_key_exists('path', $body)
            ) {
                throw new RequestException("Request to start transfer ({$name}) missing 'path' element");
            }
            $output = $body['path'];
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function unapproved(): array
    {
        $output = [];
        try {
            $response = $this->am_client->get(
                '/api/v2/transfer/unapproved/'
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request to list transfers failed"
            );
            if (
                !is_array($body) ||
                !array_key_exists('message', $body) ||
                strcasecmp(
                    'Fetched unapproved transfers successfully.',
                    $body['message']
                )
            ) {
                throw new RequestException("Request to list transfers missing expected message");
            }
            $output = $body['results'];
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function approve(string $directory, string $type): string
    {
        ArchivmaticaUtils::isValidTransferType($type);
        $output = "";
        try {
            $response = $this->am_client->post(
                '/api/v2/transfer/approve/',
                [
                    'form_params' => [
                        'type' => $type,
                        'directory' => $directory,
                    ],
                ]
            );
            $body = Utils\ArchivmaticaUtils::checkResponse(
                $response,
                200,
                "Request to approve directory ({$directory}) failed"
            );
            $this->logger->debug("Approve body", $body);
            if (
                !is_array($body) || !array_key_exists('message', $body) ||
                array_key_exists('error', $body) ||
                $body['message'] !== 'Approval successful.'
            ) {
                throw new RequestException("Request to approve directory ({$directory}) failed");
            }
            $output = $body['uuid'];
        } catch (GuzzleException $e) {
            Utils\ArchivmaticaUtils::decodeGuzzleException($e);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $uuid): array
    {
        return parent::internalStatus($uuid, "transfer");
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $uuid): void
    {
        parent::internalDelete($uuid, "transfer");
    }

    /**
     * {@inheritDoc}
     */
    public function completed(): array
    {
        return parent::internalCompleted("transfer");
    }

    /**
     * {@inheritDoc}
     */
    public function reingest(string $name, string $uuid): string
    {
        return parent::internalReingest($name, $uuid, "transfer");
    }

    /**
     * @param array $data
     *   Array of local uuid (keys) => relative paths (values)
     * @return array
     *   Base64 encoded key:value
     */
    private static function getEncodedPaths(array $data): array
    {
        array_walk($data, function (&$o, $k) {
            $o = base64_encode("$k:$o");
        });
        return array_values($data);
    }
}
