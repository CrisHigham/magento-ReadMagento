# ReadMagento

### Purpose

Large Magento installations can be complicated webs of abstracted code. This repository, is to assist navigating a Magento installation to make the web of code a little more understandable.

### How to Use

Drop the file into the root of the Magento installation. Run http://mydomain.com/ReadMaagento.php in your web browser.

### Current Functionality

This tool, is purposefully designed as a single file app. The file can be dropped into the root of a Magento installation, and can be called through a web browser as a self standing page. The page calls itself, using links to determine the functionality.

##### Plugins

In the Magento space, plugins are declared in the di.xml file in the etc folder for each module. Part of that declaration is the module that is being observered. However this means a module, can have numerous plugins operatining on it, from through out the codebase. The Plugins link combines all plugins acting on a module allowing further code investigation.

##### Endpoints

In the Magento space, endpoints are declared in the webapi.xml file in the etc folder for each module. The Endpoints link finds all the available endpoints.

