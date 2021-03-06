<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Game
 * @package App\Models
 * @property int id
 * @property string name
 * @property int last_update
 * @property string loader_path
 * @property string dll_path
 * @property int status
 * @property double reset_cost
 */
class Game extends Model
{
    public $timestamps = false;

}
