<?php

namespace App\Helpers;

class ResponseHelper{
	public const NOT_FOUND = 'Not found';
	private static function build(bool $fulfilled, mixed $data, mixed $errors, mixed $pagination){
		$response['fulfilled'] = $fulfilled;
		if(!empty($data)){
			$response['data'] = $data;
		}
		if(!empty($errors)){
			$response['errors'] = $errors;
		}
		if(!empty($pagination)){
			$response['pagi$pagination'] = $pagination;
		}
		return $response;
	}
	public static function buildError(mixed $errors){
		return self::build(\false, \null, $errors, \null);
	}
}

