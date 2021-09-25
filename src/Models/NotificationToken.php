<?php

namespace Ismat\Notifications\Models;

use App\Models\Application;
use App\Models\User;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property integer $user_id
 * @property integer $application_id
 * @property string $application_name
 * @property string $fcm_token
 * @property string $user_ip
 * @property string $user_agent
 * @property string $created_at
 * @property string $updated_at
 * @property Application $application
 * @property User $user
 */
class NotificationToken extends Model
{
    use UsesUuid;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'application_id', 'application_name', 'fcm_token', 'user_ip', 'user_agent', 'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
