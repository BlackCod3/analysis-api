analysis-api
============

This is an app that manage measure (see documentation https://analytics.wizbii.com/doc.html)

## Install ##

	$ git clone git@github.com:BlackCod3/analysis-api.git

	$ cd analysis-api

	$ composer install

	$ php bin/console server:start 


## What's inside ? ##

* measure-recorder-client: https://github.com/BlackCod3/measure-recorder-client
* And some symfony bundles..

## Documentation ##

See the api documentation on : localhost:%port%/documentation


## Try it ##

	$ curl -X GET 'localhost:%port%/collect' 
	
	$  curl -X POST -d '[{"t":"event","dl":"company/wizbii","dr":"job/dev-backend-chez-wizbii" ,"wct":"profile", "wui":"robert-k","ec":"navigation", "ea":"tap","el":"button-top","ev":1,"tid":"UA-12345-Y", "ds":"apps","sn":"company","an":"WizbiiStudentAndroid","av":"1.2.1","qt":"1230","v":1, "wuui": "52fhe52e65e1f4f2"}]' 'localhost:%port%/collect'
