<?php


namespace Ismat\Notifications;


use Illuminate\Support\Manager;
use Ismat\Notifications\Drivers\Firebase;

class NotificationManager extends Manager
{
    /**
     * Get instance of driver
     * @param  string|null  $name
     * @return mixed
     */
    public function channel($name = null)
    {
        return $this->driver($name);
    }


    /**
     * Create a Firebase notification driver instance.
     *
     * @return Firebase
     */
    public function createFirebaseDriver()
    {
        return new Firebase;
    }

    /**
     * Get the default notification driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('iNotification.default_driver');
    }


}
