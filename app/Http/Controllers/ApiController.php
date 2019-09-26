<?php
namespace App\Http\Controllers;

use Illuminate\Http\Response as IlluminateResponse;

//use App\Equio\CustomException;

/**
 * Class ApiController
 */
class ApiController extends Controller
{

    /**
     * @var
     */
    protected $statusCode = IlluminateResponse::HTTP_OK;

    /**
     * @param array $data
     * @return mixed
     */
    public function respondCreated($data = array())
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respond($data);
    }

    /**added custom custom expection*/

    public function __construct()
    {
       // set_error_handler( [$this,"exception_custom_error_handler"]);
    }


    /**
     *
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        // Returns the object for method chaining.
        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)
            ->respondWithError($message);
    }

    /**
     * @param $message
     * @param array $data
     * @return mixed
     */
    public function respondWithError($message, array $data = array())
    {
        $message = ['status' => 'false', 'message' => $message, 'status_code' => $this->getStatusCode()];
        $data = array_merge($message, $data);

        return response()->json($data, $this->getStatusCode());
    }

    /**
     * @param $message
     * @return mixed
     */
    public function respondUnauthorized($message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)
            ->respondWithError($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondBadRequest($message = 'Bad Request!', $data = array())
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)
            ->respondWithError($message, $data);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondInternalServerError($message = 'Internal Server Error !')
    {
        return $this->setStatusCode
        (IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->respondWithError($message);
    }

    public function  exception_custom_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new CustomException($errstr, 0, $errno, $errfile, $errline);
    }


}