<?php
namespace App\Service;

use Symfony\Component\Cache\Adapter\PdoAdapter;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;

class TwitchChecker
{
    public const CACHE_EXPIRES = 120;
    private $cache;
    private $clientId;
    private $streamTag;
    private $manager;
    private $cache_handle = 'live_streams';

    public function __construct(Connection $connection, String $clientId, String $streamTag, EntityManagerInterface $manager)
    {
        $this->cache = new PdoAdapter(
            $connection,
            'twitch-',
            60
        );
        $this->clientId = $clientId;
        $this->streamTag = $streamTag;
        $this->manager = $manager;
    }

    public function getLiveStreams(Tournament $tournament)
    {
        $this->setCacheHandle( 'live_streams_' . $tournament->getId() );

        $liveStreams = $this->cache->get($this->getCacheHandle(), function(ItemInterface $item) use ($tournament) { 
            $item->expiresAfter($this::CACHE_EXPIRES);

            $twitch_users = $this->manager
                ->getRepository('App\Entity\Profile')
                ->findTournamentTwitchLinks($tournament)
            ;

            if (empty($twitch_users)) { return []; }

            $twitch_users = array_map(function($profile) {
                    $url = parse_url($profile['twitchUrl']);
                    return trim($url['path'], '/');
                }, $twitch_users
            );
    
            $query = '?user_login=' . join('&user_login=', $twitch_users);
    
            $remote_api = 'https://api.twitch.tv/helix/streams';
    
            $curl_url = $remote_api . $query;
            $header = 'Client-ID: ' . $this->clientId;
            $curl_opt = array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array($header)
            );
            $curl = curl_init();
            curl_setopt_array($curl, $curl_opt);
            $json = json_decode(curl_exec($curl));
            curl_close($curl);

            if (property_exists($json, 'data')) {
                $tag = $this->streamTag;
                $live = array_filter($json->data, function($stream) use ($tag) {
                    return (
                        $stream->type === 'live' &&
                        false !== strpos($stream->title, $tag)
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

    public function getCacheHandle()
    {
        return $this->cache_handle;
    }

    public function setCacheHandle(String $handle)
    {
        $this->cache_handle = $handle;

        return $this;
    }
}