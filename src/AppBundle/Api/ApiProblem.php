<?php

namespace AppBundle\Api;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiProblem
 * @package AppBundle\Api
 */
class ApiProblem
{

    const TYPE_VALIDATION_ERROR = 'validation_error';

    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

    /**
     * @var array
     */
    private static $titles = [
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
    ];

    private $statusCode;

    private $type;

    private $title;

    private $extraData = [];

    /**
     * ApiProblem constructor.
     * @param $statusCode
     * @param null $type
     */
    public function __construct($statusCode, $type = null)
    {
        $this->statusCode = $statusCode;

        if ($type === null) {
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title for type '.$type);
            }

            $title = self::$titles[$type];
        }

        $this->type = $type;
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->extraData,
            [
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
            ]
        );
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
