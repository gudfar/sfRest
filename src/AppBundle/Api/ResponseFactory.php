<?php

namespace AppBundle\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ResponseFactory
 * @package AppBundle\Api
 */
class ResponseFactory
{
    /**
     * @param ApiProblem $apiProblem
     * @return JsonResponse
     */
    public function createResponse(ApiProblem $apiProblem)
    {
        $data = $apiProblem->toArray();
        // making type a URL, to a temporarily fake page
        if ($data['type'] != 'about:blank') {
            $data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];
        }
        $response = new JsonResponse(
            $data,
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        return $response;
    }
}