
<?php
//namespace public_html\app\etc;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MagentoCheck
{
    const EXCLUDE_VENDORS = false;

    private $workingDirectory;
    private $resultArray;

    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $originalName = $product->getName();
        $modifiedName =
            $originalName . " - Modified by Magento 2 Events and Observers";
        $product->setName($modifiedName);
    }

    function __construct()
    {
        $this->workingDirectory = getcwd();
        $this->resultArray = [];
    }

    /**
     * Function to read the current directory
     * @param $filePath
     * @return void
     */
    function fileDir($filePath)
    {
        if (substr($filePath, 0, 1) != ".") {
            $fileArray = scandir($filePath);
            foreach ($fileArray as $file) {
                if (substr($file, 0, 1) != ".") {
                    $this->fileRead($file, $filePath);
                }
            }
        }
    }

    /**
     * Function to call the file process, or call another directory read
     * @param $file
     * @param $filePath
     * @return void
     */
    function fileRead($file, $filePath)
    {
        if (is_dir($filePath . "/" . $file)) {
            // Read the next directory level
            $this->fileDir($filePath . "/" . $file);
        } else {
            // Exclude the tests folders
            if (strpos($filePath, "/dev/tests/")) {
                return;
            }
            // Process the file
            $this->fileProcess($file, $filePath);
        }
    }

    /**
     * Function to call the respective file process, depending on the request action variable
     * @param $file
     * @param $filePath
     * @return void
     */
    function fileProcess($file, $filePath)
    {
        // Looking for plugins
        if ($_REQUEST["action"] == "plugin" && $file == "di.xml") {
            $this->findPlugins($file, $filePath);
            //            print_r($file, $filePath);
        }
        // Looking for endpoints
        if ($_REQUEST["action"] == "endpoint" && $file == "webapi.xml") {
            $this->findEndpoints($file, $filePath);
            //            print_r($file, $filePath);
        }
        // Looking for controllers

        if ($_REQUEST["action"] == "controller") {
            $this->findControllers($file, $filePath);
            //            print_r($file, $filePath);
        }
    }

    /**
     * Function to read the di.xml file and build a resultArray for plugins
     * It also sorts the plugins to show the module they act on.
     * @param $file
     * @param $filePath
     * @return void
     */
    function findPlugins($file, $filePath)
    {
        $fileContents = simplexml_load_file($filePath . "/" . $file);
        if (isset($fileContents->type)) {
            foreach ($fileContents->type as $type) {
//                print_r($type->plugin);

                if (isset($type->plugin)) {
                    /*if ((string)$type->plugin["name"] == "vaimo_dischem_maxcartqty_blockeduser")
                    {
                        echo "";
                    }*/
                    echo "Passed";
                    foreach ($type->plugin as $pluginObject) {
                        //                            echo "<p>" . $filePath . "/" . $file;
                        //                            echo "<br>";
                        //                            echo "Plugin: " . $type->plugin["name"];
                        //                            echo "<br>";
                        //                            echo "Observes: " . $type["name"];
                        //                            echo "<br>";
                        //                            echo "Executes: " . $type->plugin["type"];
                        //                            echo "</p>";
                        if (isset($pluginObject["sortOrder"])) {
                            $sortOrder = (string) $pluginObject["sortOrder"];
                        } else {
                            $sortOrder = "- ";
                        }
                        $plugin =
                            $sortOrder . " : " . (string) $pluginObject["name"];
                        $observes = (string) $type["name"];
                        $executes = (string) $pluginObject["type"];
                        $this->resultArray[$type["name"]]["name"] = $type->plugin["name"];
                        $this->resultArray[$observes][$plugin] = $executes;
                    }
                }
            }
        }
    }

    /**
     * Function to read the webapi.xml file and build a resultArray for endpoints
     * @param $file
     * @param $filePath
     * @return void
     */
    function findEndpoints($file, $filePath)
    {
        if ($file == "webapi.xml") {
            $fileContents = simplexml_load_file($filePath . "/" . $file);
            $path = $filePath . "/" . $file;
            //$this->resultArray[] = $path;
            if (isset($fileContents->route)) {
                foreach ($fileContents->route as $route) {
                    $method = (string) $route->attributes()["method"];
                    $url = (string) $route->attributes()["url"];
                    //echo "<pre>";

                    $this->resultArray[$type["name"]]["name"] = $type->plugin["name"];
//                    $this->resultArray[$path][$url] = $method;
                }
            }
        }
    }

    function findControllers($file, $filePath)
    {
        if (
            strpos($filePath, "Controller") &&
            !strpos($filePath, "Adminhtml") &&
            !strpos($filePath, "AbstractController")
        ) {
            $action = strtolower(rtrim($file, ".php"));
            $controller = strtolower(
                substr($filePath, strrpos($filePath, "Controller") + 11)
            );
            if (!empty($controller)) {
                $moduleFolder = substr(
                    $filePath,
                    0,
                    -strlen(substr($filePath, strrpos($filePath, "Controller")))
                );
                $routesPath = $moduleFolder . "/etc/frontend/routes.xml";
                if (file_exists($routesPath)) {
                    $fileContents = simplexml_load_file($routesPath);
                    $frontName = (string) $fileContents->router->route->attributes()[
                    "frontName"
                    ];
                    $this->resultArray[$moduleFolder][$frontName][
                    $controller
                    ] = $action;
                }
            }
        }
    }

    function renderPage()
    {
        $filePath = $this->workingDirectory;
        $filePath = rtrim($filePath, "/pub");
        if (self::EXCLUDE_VENDORS) {
            $filePath = $filePath . "/app/code";
        }

//        Add system paths
        echo "<p>Magento Check: " . $filePath . "</p>";
        echo "<a href='/ReadMagento.php'>Reload Page</a>";
        echo "   ";
        echo "<a href='/ReadMagento.php?action=plugin'>Plugin</a>";
        echo "   ";
        echo "<a href='/ReadMagento.php?action=endpoint'>EndPoints</a>";
        echo "   ";
        echo "<a href='/ReadMagento.php?action=controller'>CustomUrls</a>";
        echo "   ";
        echo "<br>";
//   Make it prettier
        echo "<form method='post' id='radio'>

<h3>Form</h3>


   </form>";

        // Call to build the data set
        echo "<br>";

        //        Param Request body
//        Observers
        if (isset($_GET["action"])) {
            if ($_REQUEST["action"] == "plugin") {
                echo "<pre style='font-size: 16px'>";
                var_dump($this->resultArray);

//                foreach ($this->fileDir($filePath) as $observed => $data) {
//                    echo "<p style='background-color: lightgrey'>" .
//                        $observed .
//                        " : " .
//                        count($data) .
//                        "</p>";
//                    foreach ($data as $plugin => $executes) {
//                        echo "    " . $plugin . ": " . $executes;
//                        echo "<br>";
//                    }
//                }
            }
            if ($_REQUEST["action"] == "endpoint") {
                echo "<pre style='font-size: 16px'>";
                var_dump($this->resultArray);

//                foreach ($this->fileDir($filePath) as $path => $data) {
////                    echo $path;
////                    echo "<p style='background-color: lightgrey'>" .
////                        $path .
////                        " : " .
////                        count($data) .
////                        "</p>";
////                    foreach ($data as $url => $method) {
////                        echo "    " . $method . ": " . $url;
////                        echo "<br>";
////                    }
//                }
            }
            //
            if ($_REQUEST["action"] == "controller") {
                echo "<pre style='font-size: 16px'>";
                //var_dump($this->resultArray);

                foreach ($this->fileDir($filePath) as $path => $frontNames) {
                    $result = "";
                    $i = 0;
                    foreach ($frontNames as $frontName => $controllers) {
                        foreach ($controllers as $controller => $action) {
                            $result .=
                                "    {baseUrl}/" .
                                $frontName .
                                "/" .
                                $controller .
                                "/" .
                                $action;
                            $result .= "<br>";
                            $i++;
                        }
                    }
                    $result =
                        "<p style='background-color: lightgrey'>" .
                        $path .
                        " : No of urls = " .
                        $i .
                        "</p>" .
                        $result;
                    echo $result;
                }
            }
        }
    }
}

// Calls and runs the page
(new MagentoCheck())->renderPage();
//var_dump($_REQUEST);
