<?php

declare(strict_types=1);

namespace App\Orchid\Collection;

use Illuminate\Queue\SerializesModels;
use App\Models\Documents;

class DocumentEvent
{
    use SerializesModels;

    /**
     * @var Documents
     */
    public $attachment;

    /**
     * @var int
     */
    public $time;

    /**
     * ImageAttachment constructor.
     *
     * @param Documents $documents
     * @param int        $time
     */
    public function __construct(Documents $documents, int $time)
    {
        $this->attachment = $documents;
        $this->time = $time;
    }
}
