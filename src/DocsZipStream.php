<?php

namespace UrbanBrussels\NovaApi;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class DocsZipStream
{
    public function stream(string $refnova, string $_locale = 'fr'): StreamedResponse
    {
        $listing = new DocsListing();
        $download_from_nova = new DocsDownload();
        $categories_from_nova = new DocsCategories();

        $refnova = urldecode($refnova);
        $docs = $listing->listingFromRefnova($refnova);

        $dictionary = $categories_from_nova->getCategories();
        $fallback_category = ($_locale === 'fr') ? 'Autres' : 'Anderen';

        return new StreamedResponse(
            function () use ($_locale, $dictionary, $fallback_category, $download_from_nova, $docs) {
                $opt = new \ZipStream\Option\Archive();
                $opt->setZeroHeader(true);
                $opt->setEnableZip64(false);

                $zip = new ZipStream(null, $opt);
                $versions = [];

                foreach ($docs as $doc) {
                    // Folder "version"
                    $version = array_search($doc['dossier-identifier']['key'], $versions, true);
                    if($version === false) {
                        $versions[] = $doc['dossier-identifier']['key'];
                        $version = array_key_last($versions);
                    }

                    $folder = $dictionary[$doc['category']['key']][$_locale] ?? $fallback_category;
                    \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($folder);
                    $folder = str_replace('/', '-', $folder);

                    $fp = tmpfile();
                    fwrite($fp, $download_from_nova->getDocumentStream($doc['identifier']['key']));
                    rewind($fp);
                    $zip->addFileFromStream('V.'.$version++. '/'. $folder.'/'.$doc['name']['label'], $fp);
                    fclose($fp);
                }

                $zip->finish();
            },
            200,
            [
                'Content-Disposition' => 'attachment;filename="'.str_replace('/', '-', $refnova).'.zip"',
                'Content-Type' => 'application/octet-stream',
            ]
        );
}
}