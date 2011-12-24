PHP Markup Language [![Build Status](https://secure.travis-ci.org/phpml/phpml.png)](http://travis-ci.org/phpml/phpml)
==============

Description
==============

This project is intented to be a easy-to-use and powerful template engine for PHP.
You can use PHPML with any other framework you work with, this is because it just focus
on doing what it does better, manage your templates!

Roadmap
==============
 - Index IDs, to provide faster elements search - DONE
 - Improve the tests suite
 - Build a template compiler
 - Develop more and more components
 
How to use it
===============

PHP Code
--------------
	<?php

	spl_autoload_register(function ($name) {
	    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	});
	
	try {
	
	    $tree = PHPML::getInstance()->loadTemplate('tests/_files/first_page.pml');
	    
	    $label = new Label();
	    $label->value = 'Thiago';
	    $tree->getElementById('ha')->addChild($label);
	    $tree->getElementById('img')->src = 'https://www.google.com/logos/classicplus.png';
	    
	    echo $tree;
	        
	} catch (Exception $e) {
	    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
	}
	
PHPML Code
--------------
	<?xml version="1.0" encoding="UTF-8" ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Hello world</title>
	</head>
	<body>
		<php:Register ns="phpml\components\cp" prefix="cp" />
		<php:Div id ="ha">
			<cp:Image id="img" />
		</php:Div>
	</body>
	</html>


License Information
===================

Copyright (c) 2010-2011, Thiago Rigo.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution.

* Neither the name of Thiago Rigo nor the names of its
contributors may be used to endorse or promote products derived from this
software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
