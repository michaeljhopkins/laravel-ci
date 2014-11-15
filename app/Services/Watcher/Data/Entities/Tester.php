<?php

namespace App\Services\Watcher\Data\Entities;

use Illuminate\Database\Eloquent\Model;

class Tester extends Model {

	protected $fillable = [
		'name',
		'command'
	];

}
