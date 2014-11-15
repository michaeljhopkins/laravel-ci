<?php

namespace App\Services\Watcher\Data\Entities;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model {

	protected $table = 'queue';

	protected $fillable = [
		'test_id',
	];

	public function test()
	{
		return $this->belongsTo('App\Services\Watcher\Data\Entities\Test');
	}

}
