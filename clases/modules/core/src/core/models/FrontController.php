<?php
namespace core\models;

class FrontController
{
    private $config;
    public $request;
    public $response;    
    private $layout;
    static $instance;
    
    public static function getInstance()
    {
        if(!self::$instance)
            self::$instance = new FrontController();
        
        return self::$instance; 
            
    } 
    
    private function __construct()
    {
        $this->config = $this->getConfig();
        $this->request = $this->parseUrl();       
    }
    
    public function getconfig()
    {
        require_once('../configs/autoload/local.php');
        
       
        $config_local = $config;
         
        include_once('../configs/autoload/global.php');
        $config_global = $config;
    
        $config = array_merge($config_global, $config_local);
        
        return $config;
    }
    
    public function parseUrl()
    {
        $actions = array('users'=>array('select','insert','delete', 'update'),
             
        );
    
        $request = array();
        // Dividir el string por /
        $request = explode("/", $_SERVER['REQUEST_URI']);
    
    
        if($request[1]=='')
            return array('controller'=>'application\\controllers\\index',
                'action'=>'index'
            );
    
            // Mientras que ultimo elemento vacio, eliminarlo
            while($request[count($request)-1]=='')
                unset($request[count($request)-1]);
    
            // Si longitud superior a 3 y par error 412
            if(count($request) > 3 && (count($request)%2) == 0 )
                return array('controller'=>'application\\controllers\\error',
                    'action'=>'412'
                );
    
                // De lo contrario hacer array de params
                $params = array();
                for($a=3;$a<count($request);$a+=2)
                {
                    $params[$request[$a]]=$request[$a+1];
                }
    
    
                // If file_exist controller && controller not ''
                if(file_exists($_SERVER['DOCUMENT_ROOT'].
                    '/../modules/application/src/application/controllers/'.
                    $request[1].'.php') &&
                    $request[1]!='')
                {
                    // If action in array && not ''
                    // Ok
                    // Return request
                    $controller = $request[1];
                    if(!isset($request[2]))
                        return array('controller'=>'application\\controllers\\'.$controller,
                            'action'=> 'index'
                        );
    
                        if(in_array($request[2], $actions[$request[1]]) && $request[2]!='')
                        {
                            $action = $request[2];
                            return array('controller'=>'application\\controllers\\'.$controller,
                                'action'=> $action,
                                'params'=>$params
                            );
                        }
                        else if($request[2]=='')
                        {
                            return array('controller'=>'application\\controllers\\'.$controller,
                                'action'=> 'index',
                                'params'=>$params
                            );
                        }
                        else
                        {
                            return array('controller'=>'application\\controllers\\error',
                                'action'=> '404'
                            );
                        }
    
    
                }
                else
                {
                    return array('controller'=>'application\\controllers\\error',
                        'action'=> '404'
                    );
                }
                 
    }
    
    public function dispatch()
    {
        $controller = $this->request['controller'];
        $action = $this->request['action'];
        
        $controller = new $controller($this);
        $this->layout = $controller->layout;
        
        $this->response = $controller->$action();
        $this->renderLayout();
        
        return $this;
        
    }
    
    public function renderLayout()
    {
        $content = $this->response;
        include('../modules/application/src/application/layouts/'.$this->layout.'.phtml');
        return ;
    }
}
