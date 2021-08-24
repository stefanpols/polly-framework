<?php

namespace Polly\ORM\Types;

use JsonException;
use Polly\ORM\Interfaces\IReferenceType;
use stdClass;

class Json implements IReferenceType
{

    private string $data;

    /**
     * Json constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        if(!$this->isJson($data))
        {
            throw new JsonException();
        }
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isJson($data)
    {
        json_decode($data);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @param string|null $dbValue
     * @return Json|null
     */
    public static function createFromDb(?string $dbValue)
    {
        if(is_null($dbValue)) return null;
        return new Json($dbValue);
    }

    /**
     * @param array $array
     * @return Json
     */
    public static function createFromArray(array $array): Json
    {
        return new Json(json_encode(array_values($array)));
    }

    /**
     * @param array $array
     * @return Json
     */
    public static function createFromObject(array $array): Json
    {
        return new Json(json_encode($array));
    }

    /**
     * @return stdClass|null
     */
    public function asObjects(): ?stdClass
    {
        return json_decode($this->data);
    }

    /**
     * @return array|null
     */
    public function asArray(): ?array
    {
        return json_decode($this->data, true);
    }

    /**
     * @return string
     */
    public function parseToDb() : ?string
    {
        return $this->asString();
    }

    /**
     * @return array|null
     */
    public function asString(): ?string
    {
        return $this->data;
    }




}