<?php
//set the namespace
namespace BytesPhp\Rest\Server\Types\Context;

//add namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Reflection\Extensibility\ExtensionsManager as ExtensionsManager;

//import internal namespace(s) required
use BytesPhp\Rest\Server\Server as Server;
use BytesPhp\Rest\Server\Types\Configuration as Configuration;

//the application context class
class ApplicationContext {

    //private properties
    private Server $server;
    private Configuration $config;

    private array $exInterfaces = ["BytesPhp\Rest\Server\API\IEndpointExtension"];
    private array $extensions = [];

    //constructur method
    public function __construct(Server $server, Configuration $config) {

        //set the properties
        $this->server = $server;
        $this->config = $config;

        //load the extensions
        $this->loadExtensions($this->config->searchPaths);

    }

    //(public) getter (magic) method, for read-only properties
    public function __get(string $property) {
            
        switch(strtolower($property)) {

            case "configuration":
                return $this->config;
                break;

            case "extensions":
                return $this->extensions;
                break;

            case "endpoints":
                return $this->getEntpoints();
                break;
                
            default:
                return null;
            
        }
        
    }

    //load all extensions (found)
    private function loadExtensions(array $searchPaths):void {

        $exManager = new ExtensionsManager(); //create a new extensions manager class instance

        $output = [];

        foreach($this->exInterfaces as $interface){ //loop for each interface known

            $output[$interface] = $exManager->GetExtensions($searchPaths,$interface);

        }

        $this->extensions = $output;

    }

    //get all enabled endpoint extensions
    private function getEntpoints() {

        $output = [];

        foreach($this->config->endpoints as $route => $className) { //loop for each endpoint definition in the configuration

            foreach($this->extensions["BytesPhp\Rest\Server\API\IEndpointExtension"] as $extension) { //loop for each extension found (in search paths)

                if($extension->className == $className) { //the class names are matching

                    //initialize the entpoint handler class instance
                    $instance = $extension->instance;
                    $instance->Initialize($this,$extension->metadata);

                    //clean the route
                    $cleanRoute = "/".trim($route,"/");

                    //add extension to output
                    $output[$cleanRoute] = $instance;

                }

            }

        }

        return $output;

    }

}
?>