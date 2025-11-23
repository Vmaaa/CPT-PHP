<?php
class ServerSpecifics
{
    protected $API_URL;

    protected $WEBPAGE_URL;

    //DB connection for telat
    protected $dbHost;
    protected $dbUser;
    protected $dbPwd;
    protected $dbName;

    // JWT specifics
    protected  $jwtDurationTelat = 3600 * 24; // 24 hours

    private static $instance = null;

    private function __construct()
    {
        $this->API_URL  = getenv('API_URL');
        $this->WEBPAGE_URL = getenv('WEBPAGE_URL');
        $this->dbHost = getenv('DB_HOST');
        $this->dbUser = getenv('DB_USER');
        $this->dbPwd  = getenv('DB_PASSWORD');
        $this->dbName = getenv('DB_NAME');
    }

    public static function getInstance() : ServerSpecifics
    {
        if (self::$instance == null) {
            self::$instance = new ServerSpecifics();
        }
        return self::$instance;
    }


    public function fnt_getDBConnection() : mysqli | bool
    {
        $dbconn = new mysqli(
            $this->dbHost,
            $this->dbUser,
            $this->dbPwd,
            $this->dbName
        );
        $dbconn->set_charset('utf8');
        return $dbconn;
    }


    public function fnt_getAPIUrl() : string
    {
        return $this->API_URL;
    }

    public  function fnt_getWebPageURL() : string
    {
        return $this->WEBPAGE_URL;
    }
    
    public function fnt_getJWTDuration() : int
    {
        return $this->jwtDurationTelat;
    }

}
