<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\OutgoingMessageDomainObject;
use Evently\Models\OutgoingMessage;
use Evently\Repository\Interfaces\OutgoingMessageRepositoryInterface;

class OutgoingMessageRepository extends BaseRepository implements OutgoingMessageRepositoryInterface
{
    protected function getModel(): string
    {
        return OutgoingMessage::class;
    }

    public function getDomainObject(): string
    {
        return OutgoingMessageDomainObject::class;
    }
}
