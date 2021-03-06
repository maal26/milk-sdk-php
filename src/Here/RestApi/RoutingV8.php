<?php

namespace HiFolks\Milk\Here\RestApi;

use HiFolks\Milk\Here\RestApi\Common\RestClient;
use HiFolks\Milk\Here\RestApi\Common\RestConfig;
use HiFolks\Milk\Here\Common\LatLong;

/**
 * Class RoutingV8
 * @package HiFolks\Milk\Here\RestApi
 */
class RoutingV8 extends RestClient
{

    private const BASE_URL = "https://router.hereapi.com";
    /**
     *  @property string $routingMode Specifies which optimization is applied during route calculation.
     * Enum [ fast | short ]
     */
    private $paramRoutingMode;
    /**
     *
     * Mode of transport to be used for the calculation of the route.
     * Enum [car | pedestrian | truck ]
     */
    private $paramTransportMode;


    /**
     * @var array
     */
    private $paramReturn;

    /**
     * @var string
     */
    private $paramLang;


    /**
     * @var LatLong|null
     */
    private $origin = null;
    /**
     * @var LatLong|null
     */
    private $destination = null;


    private const ENV_ROUTING_V8 = "ENV_ROUTING_V8";


    public function __construct()
    {
        parent::__construct();
        $this->reset();
    }

    public static function instance($apiToken = ""): self
    {
        return self::config(RestConfig::getInstance($apiToken, self::BASE_URL, self::ENV_ROUTING_V8));
    }

    public static function config(RestConfig $c): self
    {
        $routing = new self();
        $routing->c = $c;
        return $routing;
    }

    public static function setToken(string $token): self
    {
        $routing = self::config(RestConfig::getInstance("", self::BASE_URL, self::ENV_ROUTING_V8));
        $routing->c->setToken($token);
        return $routing;
    }

    public function reset()
    {
        parent::reset();

        $this->contentType = "";
        $this->acceptContentType = "";
        $this->paramTransportMode = "car";
        $this->paramRoutingMode = "fast";
        $this->paramReturn = [];
        $this->paramLang = "";

        $this->origin = null;
        $this->destination = null;
    }


    public static function setApiKey(string $apiKey): self
    {
        $space = self::config(RestConfig::getInstance("", self::BASE_URL, self::ENV_ROUTING_V8));
        $space->c->setApiKey($apiKey);
        return $space;
    }



    private function routingMode($mode): self
    {
        $this->paramRoutingMode = $mode;
        return $this;
    }
    /**
     * Route calculation from start to destination optimized by travel time
     */
    public function routingModeFast(): self
    {
        return $this->routingMode("fast");
    }
    /**
     * Route calculation from start to destination disregarding any speed information
     * @return self
     */
    public function routingModeShort(): self
    {
        return $this->routingMode("short");
    }


    private function transportMode($mode): self
    {
        $this->paramTransportMode = $mode;
        return $this;
    }
    public function byFoot(): self
    {
        return $this->transportMode("pedestrian");
    }
    public function byCar(): self
    {
        return $this->transportMode("car");
    }
    public function byTrack(): self
    {
        return $this->transportMode("truck");
    }


    public function returnInstructions(): self
    {
        $this->paramReturn[] = "polyline";
        $this->paramReturn[] = "actions";
        $this->paramReturn[] = "instructions";
        return $this;
    }
    public function lang($lang): self
    {
        $this->paramLang = $lang;
        return $this;
    }
    public function langIta(): self
    {
        return $this->lang("it-IT");
    }




    public function startingPoint(float $latitude, float $longitude): self
    {
        return $this->startingPointLatLong(new LatLong($latitude, $longitude));
    }

    public function startingPointLatLong(LatLong $latLong): self
    {
        $this->origin = $latLong;
        return $this;
    }
    public function destination(float $latitude, float $longitude): self
    {
        return $this->destinationLatLong(new LatLong($latitude, $longitude));
    }
    public function destinationLatLong(LatLong $latLong): self
    {
        $this->destination = $latLong;
        return $this;
    }



    protected function queryString(): string
    {
        $retString = "";


        if ($this->paramRoutingMode) {
            $retString = $this->addQueryParam($retString, "routingMode", $this->paramRoutingMode);
        }
        if ($this->paramTransportMode) {
            $retString = $this->addQueryParam($retString, "transportMode", $this->paramTransportMode);
        }

        if (count($this->paramReturn) > 0) {
            $retString = $this->addQueryParam($retString, "return", implode(",", $this->paramReturn));
        }

        if ($this->paramLang !== "") {
            $retString = $this->addQueryParam($retString, "lang", $this->paramLang);
        }


        if ($this->origin) {
            $retString = $this->addQueryParam($retString, "origin", $this->origin->getString(), false);
        }
        if ($this->destination) {
            $retString = $this->addQueryParam($retString, "destination", $this->destination->getString(), false);
        }

        $cred = $this->c->getCredentials();
        if (! $cred->isBearer()) {
            if ($cred->getApiKey() !== "") {
                $retString = $this->addQueryParam($retString, "apiKey", $cred->getApiKey());
            }
            if ($cred->getAppId() !== "" && $cred->getAppCode() !== "") {
                $retString = $this->addQueryParam($retString, "app_id", $cred->getAppId());
                $retString = $this->addQueryParam($retString, "app_code", $cred->getAppCode());
            }
        }

        return $retString;
    }

    /*
    public function getManeuverInstructions()
    {
        $array = [];
        $result = $this->get();
        return $result->response->route[0]->leg[0]->maneuver;
    }
    */

    protected function getPath(): string
    {
        $retPath = "";
        $retPath = "/v8/routes";
        return $retPath;
    }
}
