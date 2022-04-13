<?php

namespace UrbanBrussels\NovaApi;

class DocsDownload
{
    public function getDocumentStream(string $identifier): string
    {
        // Get Documents
        $nova_connection_docs = new NovaConnection(
            $_ENV['NOVA_API_ENDPOINT'],
            $_ENV['NOVA_API_CONSUMER_KEY'],
            $_ENV['NOVA_API_CONSUMER_SECRET'],
            'NOVA_API_DOCUMENT'
        );

        $nova_connection_docs->setJwtKey($_ENV['NOVA_API_JWT_EXTERNALS']);

        return (new RestrictedData($nova_connection_docs))->downloadDocument($identifier);
    }
}