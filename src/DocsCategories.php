<?php

namespace UrbanBrussels\NovaApi;

use JsonException;

class DocsCategories
{
    /**
     * @throws JsonException
     */
    public function getCategories(): array
    {
        $dictionary = file_get_contents(__DIR__ .'/assets/nova_dictionary.json');
        $categories = json_decode($dictionary, false, 512, JSON_THROW_ON_ERROR);

        $data = [];

        foreach($categories->elements as $category) {

            foreach($category->labels->translations as $translation) {
                $lang = strtolower($translation->language);
                if(isset($translation->label)) {
                    $data[$category->identifier->key][$lang] = $translation->label;
                }
            }

        }

        return $data;
    }
}