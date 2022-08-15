<?php

namespace Aljerom\Albion\Services;

use GuzzleHttp\RequestOptions;
use MagicPro\Config\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class AlbionApi
{
    private const API_BASE_URL = 'https://gameinfo.albiononline.com/api/gameinfo/';

    /**
     * @var Client
     */
    private $client;

    private $data = [];

    private $returnArray = false;

    public function __construct()
    {
        Config::get('albion');

        $this->client = new Client(
            [
                'base_uri' => self::API_BASE_URL,
                'timeout' => 10,
            ]
        );
    }

    public function setReturnArray()
    {
        $this->returnArray = true;

        return $this;
    }

    public function makeRequest(string $method, string $uri, array $parameters = []): void
    {
        try {
            if ($method === 'get') {
                $parameters['t'] = time();
            }
            $parameters[RequestOptions::CONNECT_TIMEOUT] = 3;
            $guzzleResult = $this->client->request($method, $uri, $parameters);

            $this->data = json_decode($guzzleResult->getBody(), $this->returnArray);
        } catch (ClientException $e) {
            $this->data = [];
        }
    }

    /**
     * Returns value currently stored in $data
     *
     * @return array|object
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * Returns first value currently stored in $data
     *
     * @return array
     */
    public function first()
    {
        if (is_array($this->data) && isset($this->data[0])) {
            return $this->data[0];
        }

        return $this->data;
    }

    public function search(array $parameters)
    {
        $this->makeRequest(
            'get',
            'search',
            [
                'query' => $parameters
            ]
        );

        return $this;
    }

    public function players()
    {
        $this->data = $this->returnArray ? $this->data['players'] : $this->data->players;

        return $this;
    }

    public function guilds()
    {
        $this->data = $this->returnArray ? $this->data['guilds'] : $this->data->guilds;

        return $this;
    }

    public function playerInfo(string $playerId)
    {
        $this->makeRequest('get', 'players/' . $playerId);

        return $this;
    }

    public function guildInfo(string $guildId)
    {
        $this->makeRequest('get', 'guilds/' . $guildId);

        return $this;
    }

    public function guildData(string $guildId)
    {
        $this->makeRequest('GET', 'guilds/' . $guildId . '/data');

        return $this;
    }

    public function guildMembers(string $guildId)
    {
        $this->makeRequest('GET', 'guilds/' . $guildId . '/members');

        return $this;
    }

    public function guildStats(string $guildId)
    {
        $this->makeRequest('GET', 'guilds/' . $guildId . '/stats');

        return $this;
    }

    public function guildTopKills(string $guildId, $parameters = [])
    {
        $this->makeRequest(
            'GET',
            'guilds/' . $guildId . '/top',
            [
                'query' => $parameters
            ]
        );

        return $this;
    }

    public function guildFued(string $guildId, string $secondGuildId)
    {
        $this->makeRequest('GET', 'guilds/' . $guildId . '/fued/' . $secondGuildId);

        return $this;
    }

    public function serverStatus()
    {
        $this->makeRequest('get', 'http://live.albiononline.com/status.txt');

        return $this;
    }

    public function recentEvents($parameters = [])
    {
        $this->makeRequest(
            'get',
            'events',
            [
                'query' => $parameters
            ]
        );

        return $this;
    }

    public function eventDetails(int $eventId)
    {
        $this->makeRequest('get', 'events/' . $eventId);

        return $this;
    }

    public function eventHistory(int $eventId, int $secondEventId)
    {
        $this->makeRequest('get', 'events/' . $eventId . '/history/' . $secondEventId);

        return $this;
    }
}
