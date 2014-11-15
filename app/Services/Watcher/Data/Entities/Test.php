<?php

namespace App\Services\Watcher\Data\Entities;

use Illuminate\Database\Eloquent\Model;

class Test extends Model {

	protected $fillable = [
		'suite_id',
		'name'
	];

}
