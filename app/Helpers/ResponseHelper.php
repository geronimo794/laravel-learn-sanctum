<?php

namespace App\Helpers;

class ResponseHelper{
	public const NOT_FOUND = 'Not found';
	public const INVALID = 'Invalid';
	public const SAVE_FAILED = 'Save failed';
	public const UNAUTHORIZE = 'Unauthorize';
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
	public static function buildNotFound(){
		return self::build(\false, \null, ['data'=>[self::NOT_FOUND]], \null);
	}
	public static function buildUnauthorize(){
		return self::build(\false, \null, ['user'=>[self::UNAUTHORIZE]], \null);
	}

	public static function buildSuccess(mixed $data){
		return self::build(\true, $data, \null, \null);
	}
	public static function buildSuccessWithPagination(mixed $data, mixed $pagination){
		return self::build(\true, $data, \null, $pagination);
	}

}

