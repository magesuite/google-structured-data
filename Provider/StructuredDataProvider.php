<?php
namespace MageSuite\GoogleStructuredData\Provider;

class StructuredDataProvider
{

    private $data = [];

    public function structuredData()
    {
        return $this->data;
    }


    public function add($data)
    {
        foreach ($data as $key => $value) {
            $this->addKey($key, $value);
        }

        return $this->data;
    }

    public function addKey($key, $value)
    {
        $this->data[$key] = $value;

        return $this->data[$key];
    }


    public function removeKey($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Destruct registry items
     */
    public function __destruct()
    {
        $this->data = [];
    }
}