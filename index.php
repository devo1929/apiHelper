<?php

$apiHelper = new ApiHelper();
if ($apiHelper->IsShowDocs()) {
    // user is accessing the base page, show them the documentation
    include("doc/index.php");
    return;
}

if (!$apiHelper->IsValidObject()) {
    // an invalid object is referenced in the url
    ApiHelper::NotFound();
}

$apiHelper->CallObject();

/**
 * Class ApiHelper
 */
class ApiHelper
{

    private static $MAX_LIMIT = 250;
    /**
     * @var string GET, POST, PUT, DELETE
     */
    public $verb;

    /**
     * @var array url components
     */
    public $components;

    /**
     * @var string the object name
     */
    public $object;

    /**
     * @var number the id of the current object
     */
    public $id;

    public $options;

    public $data;

    public function __construct()
    {
        $request = trim($_SERVER["REQUEST_URI"], '/');

        $this->components = explode('/', $request);
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $this->object = count($this->components) > 0 ? $this->components[0] : null;

        $this->data = json_decode(file_get_contents("php://input"), true);
        $this->ConfigureId();
        $this->ConfigureQueryParams();
    }

    private function ConfigureId()
    {
        $this->id = count($this->components) > 1 ? $this->components[count($this->components) - 1] : null;
        $this->ValidateId();
        $this->id = intval($this->id);
    }

    private function ValidateId()
    {
        if (isset($this->id)) {
            if (!is_numeric($this->id) || intval($this->id) < 1) {
                self::BadRequest("Invalid id");
            }
        }
    }

    private function ConfigureQueryParams()
    {
        $this->options = $_GET;
        $this->ValidateQueryParams();
        if (isset($this->options["page"])) {
            $this->options["page"] = intval($this->options["page"]) - 1;
        }
    }

    /**
     * @return bool a valid object was specified
     */
    public function IsValidObject()
    {
        return $this->object != null && file_exists($this->object);
    }

    public function IsSingleObject()
    {
        return $this->id != null;
    }

    /**
     * Includes the object and calls its verb function
     */
    public function CallObject()
    {
        /** @noinspection PhpIncludeInspection */
        include("$this->object/index.php");
        if (!function_exists($this->verb)) {
            self::NotImplemented();
        }
        call_user_func($this->verb);
    }

    /**
     * @return bool should show api documentation instead of processing a request/action
     */
    public function IsShowDocs()
    {
        return count($this->components) == 0 || $this->components[0] == '';
    }

    /**
     * Convenience function to return HTTP error
     * @param $code int http status code
     * @param $message string http message
     */
    public static function GeneralError($code, $message)
    {
        header("HTTP/1.1 $code $message", true, $code);
        die();
    }

    public static function NotImplemented($message = "Not Implemented")
    {
        self::GeneralError(501, $message);
    }

    /**
     * Convenience function to return 404
     * @param string $message message to display
     */
    public static function NotFound($message = "Not Found")
    {
        self::GeneralError(404, $message);
    }

    /**
     * Convenience function to return 400
     * @param string $message message to display
     */
    public static function BadRequest($message = "Bad Request")
    {
        self::GeneralError(400, $message);
    }

    /**
     * Validates all query parameters and returns http error if any errors are found.
     */
    public function ValidateQueryParams()
    {
        if (isset($this->options["page"])) {
            if (!is_numeric($this->options["page"])) {
                self::BadRequest("Invalid page");
            }
            if (intval($this->options["page"]) < 1) {
                self::BadRequest("Invalid page: cannot be less than 1");
            }
        }
        if (isset($this->options["limit"])) {
            if (!is_numeric($this->options["limit"])) {
                self::BadRequest("Invalid limit");
            }
            if (intval($this->options["limit"]) < 1) {
                self::BadRequest("Invalid limit: cannot be less than 1");
            }
        }
        if (intval($this->options["limit"]) > self::$MAX_LIMIT) {
            self::BadRequest("Invalid limit: cannot be greater than " . self::$MAX_LIMIT);
        }
    }
}


