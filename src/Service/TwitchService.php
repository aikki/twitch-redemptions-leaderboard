<?php

namespace App\Service;

use TwitchApi\Auth\OauthApi;
use TwitchApi\HelixGuzzleClient;
use TwitchApi\TwitchApi;

class TwitchService
{
    private const SCOPES = 'channel:read:redemptions';
    private $client;
    private $api;
    private $oauth;
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $baseUrl,
    )
    {
        $this->client = new HelixGuzzleClient($this->clientId);
        $this->api = new TwitchApi($this->client, $this->clientId, $this->clientSecret);
        $this->oauth = $this->api->getOauthApi();
    }

    /**
     * @return HelixGuzzleClient
     */
    public function getClient(): HelixGuzzleClient
    {
        return $this->client;
    }

    /**
     * @return TwitchApi
     */
    public function getApi(): TwitchApi
    {
        return $this->api;
    }

    /**
     * @return mixed
     */
    public function getOauth(): OauthApi
    {
        return $this->oauth;
    }

    public function getAuthUrl(string $path = ''): string
    {
        return $this->oauth->getAuthUrl($this->baseUrl . $path, 'code', self::SCOPES);
    }

    public function getUserAccessToken(string $code): \stdClass
    {
        $token = $this->oauth->getUserAccessToken($code, $this->baseUrl);
        return json_decode($token->getBody()->getContents());
    }

    public function refreshToken(string $refreshToken): \stdClass
    {
        $token = $this->oauth->refreshToken($refreshToken, self::SCOPES);
        return json_decode($token->getBody()->getContents());
    }

    public function getUserData(string $bearer): \stdClass
    {
        return json_decode($this->api->getUsersApi()->getUsers($bearer)->getBody()->getContents())->data[0];
    }

    public function getCustomRewards(string $bearer, string $broadcasterId): array
    {
        return json_decode($this->api->getChannelPointsApi()->getCustomReward($bearer, $broadcasterId)->getBody()->getContents())->data;
    }

    public function getCustomRewardRedemptions(string $bearer, string $broadcasterId, string $rewardId): array
    {
        $after = null;
        $data = [];
        for ($i = 0; $i < 500; $i++)
        {
            $contents = json_decode($this->api->getChannelPointsApi()->getCustomRewardRedemption($bearer, $broadcasterId, $rewardId, status: 'UNFULFILLED', after: $after, first: 50)->getBody()->getContents());
            $data = array_merge($data, $contents->data);
            if (property_exists($contents, 'pagination') &&
                property_exists($contents->pagination, 'cursor') &&
                !empty($contents->pagination->cursor)
            ) {
                $after = $contents->pagination->cursor;
            } else {
                break;
            }
        }
        return $data;
    }

    public function getSampleCustomRewards(): array
    {
        return json_decode('{"data":[{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"id_pierwszy","image":null,"background_color":"#00E5CB","is_enabled":true,"cost":50000,"title":"pierwszy","prompt":"","is_user_input_required":false,"max_per_stream_setting":{"is_enabled":false,"max_per_stream":0},"max_per_user_per_stream_setting":{"is_enabled":false,"max_per_user_per_stream":0},"global_cooldown_setting":{"is_enabled":false,"global_cooldown_seconds":0},"is_paused":false,"is_in_stock":true,"default_image":{"url_1x":"https://static-cdn.jtvnw.net/custom-reward-images/default-1.png","url_2x":"https://static-cdn.jtvnw.net/custom-reward-images/default-2.png","url_4x":"https://static-cdn.jtvnw.net/custom-reward-images/default-4.png"},"should_redemptions_skip_request_queue":false,"redemptions_redeemed_current_stream":null,"cooldown_expires_at":null},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"idgame","image":null,"background_color":"#00E5CB","is_enabled":true,"cost":50000,"title":"gameanalysis","prompt":"","is_user_input_required":false,"max_per_stream_setting":{"is_enabled":false,"max_per_stream":0},"max_per_user_per_stream_setting":{"is_enabled":false,"max_per_user_per_stream":0},"global_cooldown_setting":{"is_enabled":false,"global_cooldown_seconds":0},"is_paused":false,"is_in_stock":true,"default_image":{"url_1x":"https://static-cdn.jtvnw.net/custom-reward-images/default-1.png","url_2x":"https://static-cdn.jtvnw.net/custom-reward-images/default-2.png","url_4x":"https://static-cdn.jtvnw.net/custom-reward-images/default-4.png"},"should_redemptions_skip_request_queue":false,"redemptions_redeemed_current_stream":null,"cooldown_expires_at":null},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"92af127c-7326-4483-a52b-b0da0be61c01","image":null,"background_color":"#00E5CB","is_enabled":true,"cost":50000,"title":"chujtam","prompt":"","is_user_input_required":false,"max_per_stream_setting":{"is_enabled":false,"max_per_stream":0},"max_per_user_per_stream_setting":{"is_enabled":false,"max_per_user_per_stream":0},"global_cooldown_setting":{"is_enabled":false,"global_cooldown_seconds":0},"is_paused":false,"is_in_stock":true,"default_image":{"url_1x":"https://static-cdn.jtvnw.net/custom-reward-images/default-1.png","url_2x":"https://static-cdn.jtvnw.net/custom-reward-images/default-2.png","url_4x":"https://static-cdn.jtvnw.net/custom-reward-images/default-4.png"},"should_redemptions_skip_request_queue":false,"redemptions_redeemed_current_stream":null,"cooldown_expires_at":null}]}')->data;
    }

    public function getSampleCustomRewardRedemptions(): array
    {
        return json_decode('{"data":[{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zikus98","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zikus98","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zikus98","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"zabjoja","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"kubis1477","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}},{"broadcaster_name":"torpedo09","broadcaster_login":"torpedo09","broadcaster_id":"274637212","id":"17fa2df1-ad76-4804-bfa5-a40ef63efe63","user_login":"torpedo09","user_id":"274637212","user_name":"kubis1477","user_input":"","status":"CANCELED","redeemed_at":"2020-07-01T18:37:32Z","reward":{"id":"92af127c-7326-4483-a52b-b0da0be61c01","title":"gameanalysis","prompt":"","cost":50000}}],"pagination":{"cursor":"eyJiIjpudWxsLCJhIjp7IkN1cnNvciI6Ik1UZG1ZVEprWmpFdFlXUTNOaTAwT0RBMExXSm1ZVFV0WVRRd1pXWTJNMlZtWlRZelgxOHlNREl3TFRBM0xUQXhWREU0T2pNM09qTXlMakl6TXpFeU56RTFOMW89In19"}}')->data;
    }


}