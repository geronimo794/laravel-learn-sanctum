<?php

namespace App\Helpers;

class ResponseHelper{
	private static function build(int $httpStatus, bool $fulfilled, mixed $data, mixed $errors, mixed $pagination){
		$response['statusCode'] = $httpStatus;
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
	public static function buildError(int $httpStatus, mixed $errors){
		return self::build($httpStatus, \false, \null, $errors, \null);
	}
}

