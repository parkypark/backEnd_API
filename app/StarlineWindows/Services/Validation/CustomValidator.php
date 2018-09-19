<?php namespace StarlineWindows\Services\Validation;

class CustomValidator extends \Illuminate\Validation\Validator {

	public function validateText($attribute, $value, $parameters)
	{
		if(preg_match("/[^\w\s\p{P}]/", $value))
		{
			return false;
		}

		return true;
	}

}