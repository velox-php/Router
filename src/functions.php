<?php

namespace Velox\Router;

use HttpSoft\Message\Response;

function jsonResponse(mixed $data, int $status = 200): Response
{
    $response =  new Response(
        statusCode: $status,
        headers: ['content-type' => 'application/json']
    );

    $response->getBody()->write(json_encode($data));

    return $response;
}