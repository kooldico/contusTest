<?php

/**
 * Base Repository
 *
 * @name Repository
 * @vendor Contus
 * @package Base
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

namespace Contus\Base;

use BadMethodCallException;
use Contus\Base\Handlers\ValidationHandler;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Repository
{
    use ValidatesRequests, ValidationHandler;
    /**
     * The request registered on Base Repository.
     *
     * @var object
     */
    protected $request;
    /**
     * The authenticated user model.
     *
     * @var object
     */
    protected $authUser = null;
    /**
     * The class property to hold the logger object
     *
     * @var object
     */
    protected $logger;
    /**
     * @vendor Contus
     * Class constants for holding various request type handled repositories
     */
    const REQUEST_TYPE_API = 'API';
    const REQUEST_TYPE_HTTP = 'HTTP';
    /**
     * Class property for holding various request type handled repositories
     *
     * @var array
     */
    protected $requestTypes = [self::REQUEST_TYPE_HTTP, self::REQUEST_TYPE_API];
    /**
     * Class property to hold the request type
     *
     * @var string
     */
    protected $requestType = self::REQUEST_TYPE_HTTP;
    /**
     * Class property holding instance of the DatabaseManager
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db = null;

    /**
     * Class contructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->request = app()->make('request');
        $this->logger = app()->make('log');
        $this->db = app()->make('db');

        if (!isMobile()) {
            $this->setAuthUser();
        }
    }

    /**
     * Set the auth user to class property
     */
    public function setAuthUser()
    {
        if ($authUser = app()->make('auth')->user()) {
            $this->authUser = $authUser;
        }
    }

    /**
     * Create the response for when a request fails validation.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $errors
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {

        if ($request->ajax() || $request->wantsJson() || $this->requestType == static::REQUEST_TYPE_API) {
            return new JsonResponse(['error' => true, 'statusCode' => 422, 'message' => (($this->request->header('x-request-type') == 'mobile') ? array_shift($errors)[0] : $errors)], 422);
        }

        return redirect()->to($this->getRedirectUrl())->withInput($request->input())->withErrors($errors, $this->errorBag());
    }

    /**
     * Get the property name through method name
     *
     * @param string $methodName
     * @return string
     *
     */
    private function getExpectedPropertyName($methodName)
    {
        return lcfirst(substr($methodName, 3));
    }

    /**
     * Magic Method helps to define and get the class property with actual methods
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        $classProperty = $this->getExpectedPropertyName($method);

        if (!property_exists($this, $classProperty)) {
            throw new BadMethodCallException("Method [$method] does not exist.");
        }

        switch (substr($method, 0, 3)) {
            case 'get':
                return $this->{$classProperty};
            case 'set':
                $propertyValue = array_shift($parameters);
                $this->{$classProperty} = $propertyValue;
                break;
            default:
                throw new BadMethodCallException("Method [$method] does not exist.");
        }

        return $this;
    }
    /**
     * This Method to find slug or id
     *
     * @return String
     */
    public function getKeySlugorId()
    {
        if (isMobile()) {
            return 'id';
        } else {
            return 'slug';
        }
    }

    /**
     * This Method to find slug or id
     *
     * @return String
     */
    public function getMongoKeySlugorId()
    {
        if (isMobile()) {
            return '_id';
        } else {
            return 'slug';
        }
    }
    /**
     * Throw new Http response as exception with json.
     * uses HttpResponseException
     *
     * @param boolean $includeFlash
     * @param int $statusCode
     * @param int $statusCode
     * @return void
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     */
    protected function throwJsonResponse($includeFlash = false, $statusCode = 404, $message = null)
    {
        $message = is_null($message) ? trans('base::general.resource_not_exist') : $message;

        if ($includeFlash) {
            $this->request->session()->flash('error', $message);
        }

        throw new HttpResponseException(new JsonResponse(['error' => true, 'statusCode' => $statusCode, 'status' => 'error', 'messages' => $message], $statusCode));
    }

}
