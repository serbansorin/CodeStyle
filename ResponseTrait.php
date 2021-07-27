<?php //app/Traits/ResponseTrait.php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use Illuminate\Support\Collection as Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use App\Transformers\BasicTransformer;

trait ResponseTrait
{
    /**
     * Status code of response
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Fractal manager instance
     *
     * @var Manager
     */
    protected $fractal;

    /**
     * Set fractal Manager instance
     *
     * @param Manager $fractal
     * @return void
     */
    public function setFractal(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Send custom data response
     *
     * @param $status
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCustomResponse($status, $message)
    {
        return response()->json(['status_code' => $status, 'message' => $message], $status);
    }


    /**
     * Send error data response
     *
     * @param $status
     * @param $message
     * @param $array
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendErrorResponse($type, $message, array $array = [])
    {
        $response = ['status_code' => 400, 'error' => ['type' => $type, 'message' => $message]];

        if(!empty($array)) {
            $response['data'] = $array;
        }        

        return response()->json($response, 400);
    }  
    
    
    /**
     * Send error data response
     *
     * @param $status
     * @param $message
     * @param $array
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSuccessResponse($type, $message, array $array = [])
    {
        $response = ['status_code' => $this->statusCode, 'message' => ['type' => $type, 'message' => $message]];

        if(!empty($array)) {
            $response['data'] = $array;
        }        

        return response()->json($response, $this->statusCode);
    }     

    /**
     * Send this response when api user provide fields that doesn't exist in our application
     *
     * @param $errors
     * @return mixed
     */
    public function sendUnknownFieldResponse($errors)
    {
        return response()->json((['status_code' => 400, 'unknown_fields' => $errors]), 400);
    }

    /**
     * Send this response when api user provide filter that doesn't exist in our application
     *
     * @param $errors
     * @return mixed
     */
    public function sendInvalidFilterResponse($errors)
    {
        return response()->json((['status_code' => 400, 'invalid_filters' => $errors]), 400);
    }

    /**
     * Send this response when api user provide incorrect data type for the field
     *
     * @param $errors
     * @return mixed
     */
    public function sendInvalidFieldResponse($errors)
    {
        return response()->json((['status_code' => 400, 'invalid_fields' => $errors]), 400);
    }

    /**
     * Send this response when a api user try access a resource that they don't belong
     *
     * @return string
     */
    public function sendForbiddenResponse()
    {
        return response()->json(['status_code' => 403, 'message' => ['type'=> 'resource_forbidden', 'text' => 'Forbidden']], 403);
        //$this->response->errorForbidden();
    }

    /**
     * Send 404 not found response
     *
     * @param string $message
     * @return string
     */
    public function sendNotFoundResponse($message = '', array $array = [])
    {
        if ($message === '') {
            $message = 'The requested resource was not found';
        }

        $response = ['status_code' => 404, 'message' => ['type'=> 'entity_not_found', 'text' => $message]];

        if(!empty($array)) {
            $response['data'] = $array;
        }

        return response()->json($response, 404);
        //return $this->response->errorNotFound();
    }

    /**
     * Send 404 not found response
     *
     * @param string $message
     * @return string
     */
    protected function sendBadRequestResponse($message = null)
    {
        return $this->response->errorBadRequest($message);
    }

    /**
     * Send 401 not found response
     *
     * @param string $message
     * @return string
     */
    protected function sendInternalResponse($message = null)
    {
        return $this->response->errorInternal($message);
    }

    /**
     * Send 403 not found response
     *
     * @param string $message
     * @return string
     */
    protected function sendUnauthorizedResponse($message = null)
    {
        return $this->response->errorUnauthorized($message);
    }


    protected function sendValidationFailedResponse($message = null, $errors = []) {
        throw new \Dingo\Api\Exception\StoreResourceFailedException($message, $errors);
    }

    /**
     * Send empty data response
     *
     * @return string
     */
    public function sendEmptyDataResponse()
    {
        return response()->json(['status_code'=> $this->statusCode, 'data' => new \StdClass()]);
    }

    /**
     * Return collection response from the application
     *
     * @param array|LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $collection
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithCollection($resource, $callback = null, $meta = [])
    {

        if(!$callback ) {
            $callback = new BasicTransformer;
        }

        $pagination_class = 'Illuminate\Pagination\LengthAwarePaginator';
        $collection_class = 'Illuminate\Support\Collection';
        $std_class = 'stdClass';

        if ($resource instanceof $pagination_class) {
        
            $response = $this->response->paginator($resource, $callback);

        } elseif ($resource instanceof $collection_class) {

            $response = $this->response->collection($resource, $callback);
        } else {

            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resource = new Collection($collection, $callback);

            $response = $this->response->array($resource);
        }

        if(!empty($meta)) {

            $oldMeta = $response->getMeta();
            $meta = array_merge($oldMeta, $meta);
            $response->setMeta($meta);
        }

        return $response;
    }


    /**
     * Return collection response from the application
     *
     * @param array|LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $collection
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithCollectionOld($collection, $callback)
    {
        $resource = new Collection($collection, $callback);

        //set empty data pagination
        if (empty($collection)) {
            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resource = new Collection($collection, $callback);
        }
        $resource->setPaginator(new IlluminatePaginatorAdapter($collection));

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }    

    /**
     * Return single item response from the application
     *
     * @param Model $item
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithItem($item, $callback =  null, $meta = [])
    {
        if(!$callback) {
            $callback = new BasicTransformer;
        }    

        $response = $this->response->item($item, $callback);

        if(!empty($meta)) {

            $oldMeta = $response->getMeta();
            $meta = array_merge($oldMeta, $meta);
            $response->setMeta($meta);
        }        

        
        return $response;
    }

    protected function respondWithItemOld($item, $callback =  null)
    {
        if($callback) {
            $resource = new Item($item, $callback);
            $rootScope = $this->fractal->createData($resource);
        } else {
            $rootScope = $item;
        }

        return $this->respondWithArray(['data' => $rootScope->toArray()]);
    }    

    /**
     * Return a json response from the application
     *
     * @param array $array
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [], $meta = [])
    {
        $array = ['status_code' => $this->statusCode, 'data' => $array];
        
        if(!empty($meta)) {
            $array['meta'] = $meta;
        }
        
        //BasicArrayTransformer
        
        $response = $this->response->array($array)->withHeaders($headers);
        
        /* Not used (not working)
        if(!empty($meta)) {

            $oldMeta = $response->getMeta();
            $meta = array_merge($oldMeta, $meta);
            $response->setMeta($meta);
        } 
        */       

        return $response;
    }
}