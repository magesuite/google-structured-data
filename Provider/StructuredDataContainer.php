<?php
namespace MageSuite\GoogleStructuredData\Provider;

class StructuredDataContainer
{
    protected $data = [];

    public function structuredData()
    {
        return $this->data;
    }


    public function add($data, $node)
    {
        foreach ($data as $key => $value) {
            $this->addKey($node, $key, $value);
        }

        return $this->data;
    }

    public function addKey($node, $key, $value)
    {
        $this->data[$node][$key] = $value;

        return $this->data[$node][$key];
    }


    public function removeKey($node, $key)
    {
        if (isset($this->data[$node]) && isset($this->data[$node][$key])) {
            unset($this->data[$node][$key]);
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