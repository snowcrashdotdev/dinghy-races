<?php
namespace App\Service;

use Symfony\Component\Cache\Adapter\PdoAdapter;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;

class TwitchChecker
{
    public const CACHE_EXPIRES = 180;
    private $cache;
    private $clientId;
    private $clientSecret;
    private $streamTag;
    private $manager;
    private $cache_handle = 'live_streams';

    public function __construct(Connection $connection, String $clientId, String $clientSecret, String $streamTag, EntityManagerInterface $manager)
    {
        $this->cache = new PdoAdapter(
            $connection,
            'twitch-',
            60
        );
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->streamTag = $streamTag;
        $this->manager = $manager;
    }

    public function getLiveStreams(Tournament $tournament)
    {
        $token = $this->getOAuthToken();

        if ($token === null) { return []; }

        $this->setCacheHandle( 'live_streams_' . $tournament->getId() );

        $liveStreams = $this->cache->get($this->getCacheHandle(), function(ItemInterface $item) use ($tournament, $token) { 
            $item->expiresAfter($this::CACHE_EXPIRES);

            $twitch_users = $this->manager
                ->getRepository('App\Entity\Profile')
                ->findTournamentTwitchLinks($tournament)
            ;

            if (empty($twitch_users)) { return []; }

            $twitch_users = array_map(
                [$this, 'pluckTwitchUsername'], $twitch_users
            );
    
            $query = '?user_login=' . join('&user_login=', $twitch_users);
    
            $remote_api = 'https://api.twitch.tv/helix/streams';
    
            $url = $remote_api . $query;

            $header = [
                'Client-ID: ' . $this->clientId,
                'Authorization: Bearer ' . $token
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (isset($response['data'])) {
                $tag = strtolower($this->streamTag);
                $live = array_filter($response['data'], function($stream) use ($tag) {
                    return (
                        $stream['type'] === 'live' &&
                        ! (strpos(strtolower($stream['title']), $tag) === false)
                    );
                });
    
                return $live;
            } else {
                return [];
            }
        });

        if (empty($liveStreams)) {
            return [];
        } else {
            return $liveStreams;
        }
    }

    private function getOAuthToken()
    {
        $token = $this->cache->get('token', function(ItemInterface $item) {
            $item->expiresAfter(4722000);

            $url = "https://id.twitch.tv/oauth2/token?client_id={$this->clientId}&client_secret={$this->clientSecret}&grant_type=client_credentials";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (isset($response['access_token'])) {
                return $response['access_token'];
            } else {
                return null;
            }
        });

        return $token;
    }

    private function getCacheHandle()
    {
        return $this->cache_handle;
    }

    private function setCacheHandle(String $handle)
    {
        $this->cache_handle = $handle;

        return $this;
    }

    protected function pluckTwitchUsername(array $result)
    {
        $url = $result['social'];
        return trim(parse_url($url)['path'], '/');
    }
}