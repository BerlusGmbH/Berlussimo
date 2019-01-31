<?php

namespace App\Libraries;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unirest\Request;
use Unirest\Request\Body;

class NchanBroadcaster extends Broadcaster
{
    /**
     *
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new broadcaster instance.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function auth($request)
    {
        if (!$request->filled('channels')) {
            throw new HttpException(403);
        }

        $channels = explode(',', $request->input('channels'));

        foreach ($channels as $channel) {
            if (Str::startsWith($channel, ['private-', 'presence-']) && !$request->user()) {
                throw new HttpException(403);
            }
            $channelName = Str::startsWith($channel, 'private-')
                ? Str::replaceFirst('private-', '', $channel)
                : Str::replaceFirst('presence-', '', $channel);
            parent::verifyUserCanAccessChannel($request, $channelName);
        }
        return "";
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (is_bool($result)) {
            return json_encode($result);
        }
        return json_encode(['channel_data' => [
            'user_id' => $request->user()->getAuthIdentifier(),
            'user_info' => $result,
        ]]);
    }

    /**
     * Broadcast the given event.
     *
     * @param  array $channels
     * @param  string $event
     * @param  array $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $payload = [
            'event' => $event,
            'channels' => collect($channels)->pluck('name'),
            'data' => $payload
        ];

        $headers = ['Accept' => 'application/json'];
        $body = Body::json($payload);
        $url = $this->config['url'] . '?' . http_build_query(['channels' => implode(',', $channels)]);

        $response = Request::post($url, $headers, $body);
    }
}