<?php namespace SignOffs\Controller;

use BaseController, DB, Input, Response;
use JWTAuth;
use SignOffs\Model\Technicians;

class LoginController extends BaseController {

  public function token()
  {
    $username = Input::get('username');
    $password = Input::get('password');

    if ($username && $password)
    {
      $user = Technicians::where('Username', $username)->whereRaw('PASSWORD(?) = Password', [$password])->get();
      if (count($user) > 0)
      {
        $data = [
          'id' => $user[0]->Id,
          'name' => $user[0]->Name,
          'token' => JWTAuth::fromUser($user[0])
        ];

        return Response::prettyjson($data);
      }
    }

    return Response::prettyjson('Login failed', 400);
  }

  public function refresh()
  {
    try
    {
        $token = JWTAuth::parseToken()->refresh();
        return Response::json(compact('token'));
    }
    catch (Exception $e)
    {
        $error = $e->getMessage();
        return Response::json(compact('error'), 400);
    }
  }

}
