<?php

namespace App\Services\Watcher\Data\Entities;

use Illuminate\Database\Eloquent\Model;

class Run extends Model {

	protected $fillable = [
		'test_id',
		'was_ok',
	    'log',
	];

}
