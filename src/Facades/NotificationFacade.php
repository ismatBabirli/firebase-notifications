<?php


namespace Ismat\Notifications\Facades;


use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * Interface NotificationFacade
 * @package App\Notification\Facades
 */
class NotificationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'notification';
    }


}
