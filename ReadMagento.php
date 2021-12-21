<?php

class MagentoCheck
{

    private $resultArray;

    function __construct()
    {
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
        }
        // Looking for endpoints
        if ($_REQUEST["action"] == "endpoint" && $file == "webapi.xml") {
            $this->findEndpoints($file, $filePath);
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
                if (isset($type->plugin)) {
                    if ((string)$type->plugin["name"] == "vaimo_dischem_maxcartqty_blockeduser")
                    {
                        echo "hello";
                    }
                    foreach ($type->plugin as $pluginObject)
                    {
                        /*    echo "<p>" . $filePath . "/" . $file;
                            echo "<br>";
                            echo "Plugin: " . $type->plugin["name"];
                            echo "<br>";
                            echo "Observes: " . $type["name"];
                            echo "<br>";
                            echo "Executes: " . $type->plugin["type"];
                            echo "</p>"; */
                        if (isset($pluginObject["sortOrder"])) {
                            $sortOrder = (string)$pluginObject["sortOrder"];
                        } else {
                            $sortOrder = "- ";
                        }
                        $plugin = $sortOrder . " : " . (string)$pluginObject["name"];
                        $observes = (string)$type["name"];
                        $executes = (string)$pluginObject["type"];

                        //$this->resultArray[$type["name"]]["name"] = $type->plugin["name"];
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
        echo "<p>" . $filePath . "/" . $file . "</p>";
    }

    function renderPage()
    {
        $filePath = getcwd();
        echo "<p>Magento Check: " . $filePath . "</p>";
        echo "<a href='/ReadMagento.php'>Reload Page</a>";
        echo "   ";
        echo "<a href='/ReadMagento.php?action=plugin'>Plugin</a>";
        echo "   ";
        echo "<a href='/ReadMagento.php?action=endpoint'>EndPoints</a>";

        $this->fileDir($filePath);
        echo "<br>";
        //$level1 = "levelone";
        //$this->resultArray[$level1]["name"] = "test";
        //print_r($this->resultArray);
        echo "<pre style='font-size: 16px'>";
        foreach ($this->resultArray as $observed => $data)
        {
            echo "<p style='background-color: lightgrey'>" . $observed . " : " . count($data) . "</p>";

            foreach ($data as $plugin => $executes)
            {
                echo "    " . $plugin . ": " . $executes;
                echo "<br>";
            }
        }
    }
}

// Calls and runs the page
(new MagentoCheck())->renderPage();
