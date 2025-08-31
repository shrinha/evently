<?php

namespace Evently\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class StripeCustomer extends BaseModel
{
    use SoftDeletes;
}
