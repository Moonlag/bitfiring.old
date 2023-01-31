<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\MimeTypes;
use Orchid\Attachment\Models\Attachmentable;
use Orchid\Filters\Filterable;
use Orchid\Platform\Dashboard;
use Orchid\Platform\Models\User;
use Orchid\Screen\AsSource;

class Documents extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $fillable = [
        'id',
        'image',
        'description',
        'status',
        'player_id',
        'disk',
        'original_name',
        'staff_id'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'url',
        'relativeUrl',
    ];


    /**
     * @var array
     */
    protected $casts = [
        'sort' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Dashboard::model(User::class));
    }

    /**
     * Return the address by which you can access the file.
     *
     * @param string|null $default
     *
     * @return string|null
     */
    public function url(string $default = null): ?string
    {
        /** @var Filesystem|Cloud $disk */
        $disk = Storage::disk($this->getAttribute('disk'));
        $path = $this->physicalPath();

        return $disk->exists($path) && $path !== null
            ? $disk->url($path)
            : $default;
    }

    /**
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        return $this->url();
    }

    /**
     * @return string|null
     */
    public function getRelativeUrlAttribute(): ?string
    {
        $url = $this->url();

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH);
    }

    /**
     * @return string|null
     */
    public function getTitleAttribute(): ?string
    {
        if ($this->original_name !== 'blob') {
            return $this->original_name;
        }

        return $this->name.'.'.$this->extension;
    }

    /**
     * @return string|null
     */
    public function physicalPath(): ?string
    {
        if ($this->image === null) {
            return null;
        }

        return $this->image;
    }

    /**
     * @throws Exception
     *
     * @return bool|null
     */
    public function delete()
    {
        if ($this->exists) {
            if (static::where('hash', $this->hash)->where('disk', $this->disk)->count() <= 1) {
                //Physical removal of all copies of a file.
                Storage::disk($this->disk)->delete($this->physicalPath());
            }
            $this->relationships()->delete();
        }

        return parent::delete();
    }

    /**
     * @return HasMany
     */
    public function relationships()
    {
        return $this->hasMany(Dashboard::model(Documents::class), 'attachment_id');
    }

    /**
     * Get MIME type for file.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        $mimes = new MimeTypes();

        $type = $mimes->getMimeType($this->getAttribute('extension'));

        return $type ?? 'unknown';
    }

    protected $table = "documents";
}
