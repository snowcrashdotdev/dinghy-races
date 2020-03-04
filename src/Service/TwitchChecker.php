<?php
namespace App\Service;

use Symfony\Component\Cache\Adapter\PdoAdapter;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Tournament;

class TwitchChecker
{
    private $cache;
    private $clientId;

    public function __construct(Connection $connection, String $clientId)
    {
        $this->cache = new PdoAdapter(
            $connection,
            'twitch-',
            60
        );
        $this->clientId = $clientId;
    }

    public function getLiveTwitchStreams(Tournament $tournament)
    {
        $liveStreams = $this->cache->get('live_streams', function(ItemInterface $item) use ($tournament) {
            $item->expiresAfter(60);
            $users = $tournament->getUsers()->filter(function($user) {
                return $user->getProfile()->hasTwitch();
            })->toArray();
    
            $twitchUsers = array_map(
                function($user) {
                    $url = parse_url($user->getProfile()->getSocial());
    
                    return trim($url['path'], '/');
                }, $users
            );

            if (empty($twitchUsers)) { return false; }
    
            $query = '?user_login=' . join('&user_login=', $twitchUsers);
    
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
            
            $live = array_filter($json->data, function($stream){
                return $stream->type === 'live';
            });

            return $live;
        });

        if (empty($liveStreams)) {
            return false;
        } else {
            return $liveStreams;
        }
    }
}