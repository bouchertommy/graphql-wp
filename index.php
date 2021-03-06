<?php
/**
* Plugin Name: WordPress GraphQL
* Plugin URI: http://www.thefold.co.nz/
* Description: GraphQL for WordPress
* Version: 0.0.1
* Author: The Fold
* Author URI: http://www.thefold.co.nz/
* License: BSD
*/
namespace TheFold\GraphQLWP;

require __DIR__.'/autoload.php';

use \GraphQL\GraphQL;
use \Exception;

const ENDPOINT = '/graphql/';

new \TheFold\WordPress\Dispatch([

    ENDPOINT => function() {

        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody ?: '', true);
        } else {
            $data = $_POST;
        }

        $requestString = isset($data['query']) ? $data['query'] : null;
        $operationName = isset($data['operation']) ? $data['operation'] : null;
        $variableValues = isset($data['variables']) ? $data['variables'] : null;

        $Schema = apply_filters('graphql-wp/schema', null) ?: new Schema();

        try {
            // Define your schema:
            $schema = $Schema->build();
            $result = GraphQL::execute(
                $schema,
                $requestString,
                /* $rootValue */ null,
                $variableValues,
                $operationName
            );
        } catch (Exception $exception) {
            $result = [
                'errors' => [
                    ['message' => $exception->getMessage()]
                ]
            ];
        }
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode($result);
    }
]);
