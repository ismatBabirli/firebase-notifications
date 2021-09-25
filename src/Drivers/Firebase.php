<?php


namespace Ismat\Notifications\Drivers;


use App;
use App\Models\Notification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Ismat\Notifications\Models\NotificationToken;

/**
 * Class Firebase
 * @package App\Notification\Drivers
 */
class Firebase extends Driver implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var int
     */
    protected $type = 1;

    /**
     * @var array
     */
    private $resultTokens;


    /**
     * Firebase constructor.
     */
    public function __construct()
    {
        $this->setDefaults();
    }

    /**
     *
     */
    public function setDefaults()
    {
        $this->driverName = 'Firebase';
        $this->lang(App::getLocale());
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->notify();
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    private function notify()
    {
        $tokens = $this->getFCMTokens();

        if (!$this->is_multilang) {
            App::setLocale($this->lang);
            $this->save();
            $tokens = collect($tokens);
            $tokens = $tokens->unique('fcm_token')
                ->pluck("fcm_token")->toArray();
            $this->push($tokens);
        } else {
            foreach ($tokens as $lang => $users) {
                App::setLocale($lang);
                $this->save();
                $userTokens = collect($users);
                $userTokens = $userTokens->unique('fcm_token')
                    ->pluck("fcm_token")
                    ->toArray();
                $this->push($userTokens);
            }
        }

        return $this;
    }

    /**
     * @return Builder[]|Collection
     */
    private function getFCMTokens()
    {
        if (isset($this->data["sender_id"]) && $sender_index = array_search($this->data["sender_id"],
                $this->recipientList, true)) {
            unset($this->recipientList[$sender_index]);
        }
        $tokens = NotificationToken::with("user")
            ->whereIn("user_id", $this->recipientList);
        if ($this->to_application) {
            $tokens = $tokens->where("application_id", $this->application);
        }
        if ($this->is_multilang) {
            $tokens = $tokens->get([
                "user_id",
                "fcm_token",
                "application_id"
            ])->groupBy("user.language");
        } else {
            $tokens = $tokens->get();
        }

        return $tokens;
    }

    /**
     * Save Notification to database
     */
    public function save(): void
    {
        if (isset($this->data['icon_user_id'])) {
            $user = App\Models\User::findOrFail($this->data['icon_user_id']);
        }
        $notification = new Notification();
        $notification->language = App::getLocale();
        if (isset($this->data["is_custom_notification"]) && $this->data["is_custom_notification"]) {
            $notification->title = $this->data['title'];
            $notification->body = $this->data['body'];
        } else {
            $notification->title = trans($this->title, $this->localizationValues);
            $notification->body = trans($this->body, $this->localizationValues);
        }
        $notification->data = $this->data;
        $notification->type = $this->type;
        $notification->media_id = isset($user) ? $user->picture_id : config("iNotification.default_icon_id");
        $notification->save();
        $this->notification = $notification;
    }

    /**
     * @param $tokens
     * @throws Exception
     */
    private function push($tokens)
    {
        if (count($tokens) === 0) {
            return;
        }
        $response = fcm()->to($tokens)
            ->priority("high")
            ->data(array_merge($this->data, [
                'title' => $this->notification->title,
                'body' => $this->notification->body
            ]))
            ->notification([
                'title' => $this->notification->title,
                'body' => $this->notification->body
            ])
            ->send();
        $this->resultTokens = $tokens;
        $this->handleResponse($response);
    }

    /**
     * @param $response
     * @throws Exception
     */
    private function handleResponse($response): void
    {
        if ($response['success'] > 0) {
            $this->setIsSuccess(true);
        }
        if ($response['failure'] > 0) {
            $this->remoteFailedTokens($response);
        }
    }

    /**
     * @param $response
     * @throws Exception
     */
    private function remoteFailedTokens($response): void
    {
        $results = $response['results'];
        $removable = [];
        foreach ($this->resultTokens as $index => $token) {
            if (isset($results[$index]["error"])) {
                $removable[] = $token;
            }
        }
        NotificationToken::whereIn("fcm_token", $removable)->delete();
    }

    /**
     * Send Notification with Dispatch Now
     */
    public function sendNow()
    {
        dispatch($this);
    }

    /**
     * Send Notification with Dispatch
     */
    public function send()
    {
        dispatch($this);
    }

    /**
     * @param $type
     * @return Firebase
     */
    public function setType($type): Firebase
    {
        $this->type = $type;
        return $this;
    }
}
