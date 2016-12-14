<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model {

	protected $table = 'rewards';
	public $timestamps = true;
	
	public function getAmountLeftAttribute(){
		$amount = $this->amount;
		$sold = \App\Payment::where('reward_id',$this->id)->where('status',1)->get();
		return (int)$amount-count($sold);
	}

	public function project(){
		return $this->belongsTo('App\Project','project_id');
	}
}