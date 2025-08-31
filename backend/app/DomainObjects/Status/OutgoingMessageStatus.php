<?php

namespace Evently\DomainObjects\Status;

enum OutgoingMessageStatus
{
    case SENT;
    case FAILED;
}
