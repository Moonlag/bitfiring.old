<?php

declare(strict_types=1);

namespace App\Orchid\Collection;

use App\Models\Documents;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Orchid\Attachment\Contracts\Engine;
use Orchid\Attachment\Engines\Generator;
use Orchid\Attachment\Models\Attachment;
use Orchid\Platform\Dashboard;
use Orchid\Platform\Events\UploadFileEvent;

class DocumentFile
{

    /**
     * @var UploadedFile
     */
    protected $file;

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @var string
     */
    protected $disk;

    /**
     * @var string|null
     */
    protected $group;

    /**
     * @var Engine
     */
    protected $engine;

    protected $player_id;

    /**
     * File constructor.
     *
     * @param UploadedFile $file
     * @param string|null $disk
     * @param string|null $group
     */
    public function __construct(UploadedFile $file, string $disk = null, string $group = null, string $player_id = null)
    {
        abort_if($file->getSize() === false, 415, 'File failed to load.');

        $this->file = $file;

        $this->disk = $disk ?? config('platform.attachment.disk', 'public');
        $this->storage = Storage::disk($this->disk);
        $this->player_id = $player_id ?? '';

        /** @var string $generator */
        $generator = DocumentGenerator::class;

        $this->engine = new $generator($file);
        $this->group = $group;
    }

    /**
     * @return Model|Attachment
     */
    public function load(): Model
    {
        $attachment = $this->getMatchesHash();

        if (!$this->storage->has($this->engine->path())) {
            $this->storage->makeDirectory($this->engine->path());
        }

        if ($attachment === null) {
            return $this->save();
        }

        $attachment = $attachment->replicate()->fill([
            'original_name' => $this->file->getClientOriginalName(),
            'user_id' => Auth::id(),
            'group' => $this->group,
        ]);

        $attachment->save();

        return $attachment;
    }

    /**
     * @return Attachment|null
     */
    private function getMatchesHash()
    {
        return Dashboard::model(Documents::class)::where('image', $this->engine->path() . $this->engine->fullName())
            ->first();
    }

    /**
     * @return Model|Attachment
     */
    private function save(): Model
    {
        $this->storage->putFileAs($this->engine->path(), $this->file, $this->engine->fullName(), [
            'mime_type' => $this->engine->mime(),
        ]);

        $attachment = Dashboard::model(Documents::class)::create([
            'image' => $this->engine->path() . $this->engine->fullName(),
            'staff_id' => Auth::id(),
            'player_id' => $this->player_id,
            'original_name' => $this->file->getClientOriginalName(),
            'status' => 1,
        ]);

        $attachment = $attachment->replicate()->fill([
            'id' => $attachment->id ?? '',
            'original_name' => $this->file->getClientOriginalName(),
            'group' => $this->group,
            'name' => $this->engine->name(),
            'mime' => $this->engine->mime(),
            'hash' => $this->engine->hash(),
            'extension' => $this->engine->extension(),
            'size' => $this->file->getSize(),
            'path' => Str::finish($this->engine->path(), '/'),
            'disk' => $this->disk,
            'user_id' => Auth::id(),
        ]);

        event(new DocumentEvent($attachment, $this->engine->time()));
        return $attachment;
    }

}
