<?php

namespace UrbanBrussels\NovaApi;

use JetBrains\PhpStorm\ArrayShape;

class DocsListing
{
    public function listingFromRefnova(string $refnova, bool $externals = true): array
    {
        $query = new PermitQuery(Permit::guessPermitType($refnova));
        $collection = $query
            ->filterByAttribute(Attribute::REFERENCE_NOVA, $refnova)
            ->setLimit(1)
            ->getResults();

        // Get Inquiry Documents (from a restricted API)
        $nova_connection_docs = new NovaConnection(
            $_ENV['NOVA_API_ENDPOINT'],
            $_ENV['NOVA_API_CONSUMER_KEY'],
            $_ENV['NOVA_API_CONSUMER_SECRET'],
            'NOVA_API_DOCUMENT');

        if($externals === true) {
            $nova_connection_docs->setJwtKey($_ENV['NOVA_API_JWT_EXTERNALS']);
        }

        $permit = $collection->getPermits()[0];

        return (new RestrictedData($nova_connection_docs))->listDocumentsFromReferences([$permit->getUuid()], 'UUID');
    }

    #[ArrayShape(['files' => "int", 'size' => "int"])]
    public function weightAndCountFromRefnova(string $refnova, bool $externals = true): array
    {
        $docs = $this->listingFromRefnova($refnova, $externals);
        $total_size = 0;

        foreach ($docs as $doc) {
            $size = explode(" ", $doc['size']);
            if($size[1] === "MB") {
                $real_size = $size[0]*1024;
            }
            elseif($size[1] === "GB") {
                $real_size = $size[0]*1024*1024;
            }
            else {
                $real_size = $size[0];
            }

            $total_size += $real_size;
        }
        return ['files' => count($docs), 'size' => $total_size];
    }
}