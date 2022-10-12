<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class AutoResponse extends Response
{
    private $autos;

    public function __construct($autos) {
        parent::__construct();
        $this->autos = $autos;
    }

    public function answer() {
        if (!empty($this->autos)) {
            $answer = $this->autos->map(function($auto) {
                return [
                    'id' => $auto->id,
                    'name' => $auto->name,
                    'assigned' => !is_null($auto->user_id)
                ];
            })->toArray();

            return ($this->shouldBeJson($answer)) ? $this->morphToJson($answer) : $answer;
        }

        return $this->morphToJson([]);
    }
}
