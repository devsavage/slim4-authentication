
# Slim 4 Authentication
An authentication system using Slim 4 (in development)

[![Latest Stable Version](http://poser.pugx.org/devsavage/slim4-authentication/v)](https://packagist.org/packages/devsavage/slim4-authentication) [![Total Downloads](http://poser.pugx.org/devsavage/slim4-authentication/downloads)](https://packagist.org/packages/devsavage/slim4-authentication) [![Latest Unstable Version](http://poser.pugx.org/devsavage/slim4-authentication/v/unstable)](https://packagist.org/packages/devsavage/slim4-authentication) [![License](http://poser.pugx.org/devsavage/slim4-authentication/license)](https://packagist.org/packages/devsavage/slim4-authentication)

## Getting Started
### Prerequisites
You will need the following to get started: 

* Web Server
	* PHP 8.1 or newer
	* URL rewriting enabled
	* SSL certification in production. Check out [HTTPS Is Easy](https://httpsiseasy.com/) for additional help. 
* [Node](https://nodejs.org/)
* [Composer](https://getcomposer.org/)

### Installation
#### Clone the project
```bash
$ git clone https://github.com/devsavage/slim4-authentication.git your-project-name
```
#### Install Composer dependencies
```bash
$ cd your-project-name && composer install
```
#### Install Node dependencies
```bash
$ npm install
```
#### Rename .env-example to .env
Update the .env file to your configuration

#### Publish database migrations
After your .env file's DB details are updated, run the command below
```bash
$ php .\vendor\bin\phinx migrate
```
#### Continued configuration
View the wiki for tips on finishing additional setup and view additional information on how to use this project.

## Vulnerabilities
Please report any vulnerabilities using the information found at: [https://savagedev.io/security.txt](https://savagedev.io/security.txt)

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
