<?php

class Database
{
    private static $dbName = 'R_601_6';
    private static $dbHost = '10.50.67.92';
    private static $dbPort = '3306';

    private static $dbUser = 'A0';
    private static $dbPassword = 'A01738341';

    /** @var PDO */
    private static $cont = null;

    public function __construct()
    {
        die('Init function is not allowed');
    }

    
     
     
      @return PDO
     
    public static function connect()
    {
        if (self::$cont === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                self::$dbHost,
                self::$dbPort,
                self::$dbName
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$cont = new PDO($dsn, self::$dbUser, self::$dbPassword, $options);
            } catch (PDOException $e) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
}



























<!-- <?php
	// class Database {
	// 	private static $dbName 					= 'TC2005B_601_6';
	// 	private static $dbHost 					= 'localhost' ;
	// 	private static $dbUsername 			= 'TC2005B_601_6';
	// 	private static $dbUserPassword 	= 'pAssWd_259756';

	// 	private static $cont  = null;

	// 	public function __construct() {
	// 		exit('Init function is not allowed');
	// 	}

	// 	public static function connect(){
	// 	   // One connection through whole application
	//     	if ( null == self::$cont ) {
	// 	    	try {
	// 	        	self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword);
	// 	        }
	// 	        catch(PDOException $e) {
	// 	        	die($e->getMessage());
	// 	        }
	//        	}
	//        	return self::$cont;
	// 	}

	// 	public static function disconnect() {
	// 		self::$cont = null;
	// 	}
	//}
?> -->
