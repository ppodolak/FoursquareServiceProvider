<?php

namespace TheTwelve\Foursquare\Silex;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TheTwelve\Foursquare\HttpClient;
use TheTwelve\Foursquare\Redirector;

class FoursquareServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container)
    {

        $container['foursquare.client'] = function($container)  {

            switch ($container['foursquare.clientKey']) {

                case 'buzz':
                    $browser = new \Buzz\Browser();
                    return new HttpClient\BuzzHttpClient($browser);

                case 'symfony':
                    // the Symfony client is preferred with silex
                    return new HttpClient\SymfonyHttpClient();

                default:
                    return new HttpClient\CurlHttpClient($container['foursquare.pathToCertificate']);

            }

        };

        $container['foursquare.redirector'] = function($container) {

            if (!isset($container['foursquare.redirectorKey'])) {
                return null;
            }

            switch ($container['foursquare.redirectorKey']) {

                case 'symfony':
                    // the Symfony client is preferred with silex
                    return new Redirector\SymfonyRedirector();

                default:
                    return new Redirector\HeaderRedirector();

            }

        };

        $container['foursquare'] = function($container) {

            $client = $container['foursquare.client'];
            $redirector = $container['foursquare.redirector'];

            $factory = new \TheTwelve\Foursquare\ApiGatewayFactory($client, $redirector);
            $factory->useVersion($container['foursquare.version']);
            $factory->setEndpointUri($container['foursquare.endpoint']);
            $factory->setClientCredentials($container['foursquare.clientId'], $container['foursquare.clientSecret']);

            return $factory;

        };

    }

}
